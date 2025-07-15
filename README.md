# laravel-mermaid-modularized
A fork of the laravel mermaid.js integration package (https://github.com/icehouse-ventures/laravel-mermaid(

The javascript inserted into the body of the html doc has been removed. The user is now required to add the jacascript init block before the closing body element.
This allows greater control over the initialize event.

---
@once
    <script src="https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js" defer></script>

    <script>
      function initMermaid() {
        if (typeof mermaid === 'undefined') {
          setTimeout(initMermaid, 100);
          return;
        }

        mermaid.initialize({ startOnLoad: true });
        mermaid.contentLoaded();
      }

      document.addEventListener('DOMContentLoaded', initMermaid);
      document.addEventListener('livewire:navigated', initMermaid);
    </script>
@endonce
---

See original repository for detailed usage information <https://github.com/icehouse-ventures/laravel-mermaid>