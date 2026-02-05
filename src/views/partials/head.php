<?php
// Theme variables based on Config primary_color
?>
<style>
:root {
    --primary: <?= htmlspecialchars(Config::get('primary_color', '#5aa8ff')) ?>;
    --primary-900: color-mix(in srgb, var(--primary) 85%, black);
    --primary-700: color-mix(in srgb, var(--primary) 70%, black);
    --primary-500: var(--primary);
    --primary-300: color-mix(in srgb, var(--primary) 60%, white);
    --primary-200: color-mix(in srgb, var(--primary) 45%, white);
    --primary-glow: color-mix(in srgb, var(--primary) 40%, transparent);
}
</style>
