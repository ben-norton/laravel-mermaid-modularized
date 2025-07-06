<?php

namespace IcehouseVentures\LaravelMermaid\Support;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Builder
{
    /**
     * @return Builder
     */
    public function build() {
        return new self();
    }

    /**
     * Generate a diagram from a PHP array
     * @return string
     */
    public static function generateDiagramFromArray(array $data, ?string $type = null, ?array $options = []): string
    {
        $diagram = self::formatArrayToLines($data);
        $diagram = self::setDiagramType($type) . $diagram;
        $diagram = self::setTheme(config('mermaid.theme')) . $diagram;
        return $diagram;
    }

    /**
     * Format an array into lines
     * @return string
     */
    protected static function formatArrayToLines(array $data): string
    {
        return Collection::make($data)->map(fn($item) => "$item;\n")->join('');
    }

    // Set the diagram type to the mermaid diagram type
    /**
     * Set the Mermaid diagram type ('graph LR', 'graph TD' etc)
     * @return string
     */
    protected static function setDiagramType(?string $type): string
    {
        return ($type ?? "flowchart LR") . ";\n";
    }

	public static function setConfiguration(): string
	{
		 return "%%{\n
	            init: {\"theme\": \"forest\", \"flowchart\": { \"htmlLabels\": \"true\", \"curve\": \"stepBefore\", }
	            }%%\n";
	}
    /**
     * Set the diagram theme
     * @return string
     */
    public static function setTheme(?string $theme = null): ?string
    {
        if (empty($theme)) {
            $theme = null;
        }

        $base_theme = $theme ?? config('mermaid.theme') ?? 'default';

        if ( in_array($base_theme, ['base', 'forest', 'dark', 'neutral', 'default'])) {
            return "%%{\n
            init: {\"theme\": \"$base_theme\", \"flowchart\": { \"htmlLabels\": \"true\", \"curve\": \"stepBefore\", }
            }%%\n";
        }

        $themeFile = $theme ?? config('mermaid.themeFile') ?? 'tailwind';

        $themeFileExtension = Str::endsWith($themeFile, '.json') ? $themeFile : $themeFile.'.json';

        $themeFilePath = __DIR__ . '/../Themes/' . $themeFileExtension;

        if (!file_exists($themeFilePath)) {
            return null;
        }

        $themeConfig = json_decode(file_get_contents($themeFilePath), true);

        $themeJson = json_encode($themeConfig);

        $themeString =
            "%%{\n
            init: $themeJson, \"flowchart\": { \"htmlLabels\": \"true\", \"curve\": \"stepBefore\", }
            }%%\n";

        return $themeString;
    }

    /**
     * Generate a diagram from a Collection of models
     * @return string
     */
    public static function generateDiagramFromCollection(Collection $models, ?string $label = null, ?string $type = null, ?array $options = []): string
    {
        $diagram = self::formatCollectionToLines($models, $label);
        $diagram = self::setConfiguration() . $diagram;
        $diagram = self::setDiagramType($type) . $diagram;
        //$diagram = self::setTheme(config('mermaid.theme')) . $diagram;
        return $diagram;
    }

    /**
     * Iterate the Eloquent models and convert to Mermaid lines
     * @return string
     */
    protected static function formatCollectionToLines(Collection $models, ?string $label = null, $parentModel = null): string
    {
        $lines = [];

        foreach ($models as $model) {

            $className = class_basename($model);
            $key = $model->getKey();
            $modelLabel = $label ? $model->{$label} : $model->name ?? $className.' '.$key;

            // Object node
            $lines[] = "{$className}{$key}[$modelLabel];\n";

            if ($parentModel !== null) {
                $parentKey = $parentModel->getKey();
                $parentClassName = class_basename($parentModel);
                // Relationship
                $lines[] = "{$parentClassName}{$parentKey} --> {$className}{$key};\n";
            }

            foreach ($model->getRelations() as $relationName => $relation) {
                // hasOne or belongsTo or morphOne
                if ($relation instanceof Model) {
                    $relatedKey = $relation->getKey();
                    $relatedLabel = $label ? $relation->{$label} : $relation->name ?? $relation->getKey();
                    $relatedClassName = class_basename($relation);

                    $lines[] = "{$relatedClassName}{$relatedKey}[$relatedLabel];\n";
                    $lines[] = "{$className}{$key} --> {$relatedClassName}{$relatedKey};\n";
                }
                // hasMany or belongsToMany or morphMany
                elseif ($relation instanceof Collection) {
                    $lines[] = self::formatCollectionToLines($relation, $label, $model);
                }
            }
        }

        return implode('', $lines);
    }
}