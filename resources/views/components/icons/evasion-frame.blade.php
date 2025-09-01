@props([
    'number' => '11',
    'label' => 'EVASION',
    'labelColor' => '#ffffff',
    'labelSize' => 58,
    'numberSize' => 150,
])

<svg
    {{ $attributes->merge([
        'class' => 'inline-block align-middle',
        'role' => 'img',
        'aria-label' => trim($label.' '.$number),
    ]) }}
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 480 498"
    preserveAspectRatio="xMidYMid meet"
    fill="none"
    style="shape-rendering:geometricPrecision;text-rendering:geometricPrecision;image-rendering:optimizeQuality;fill-rule:evenodd;clip-rule:evenodd"
>
    <!-- Center the original 410×428 art inside a 480×498 canvas (no scale). -->
    <g transform="translate(35,35)">
        <path style="opacity:.999" fill="#d3924c" d="M188.5-.5h35c55.226 7.523 97.059 35.19 125.5 83a196.549 196.549 0 0 1 18 49l23 23 1 49a8.905 8.905 0 0 1 3 4 1976.22 1976.22 0 0 0 2.5 109c-128 1.333-256 1.333-384 0 1.829-36.32 2.662-72.986 2.5-110a8.908 8.908 0 0 1 3-4l1-46a389.72 389.72 0 0 1 23-24C61.884 56.11 110.717 11.777 188.5-.5Z"/>
        <path style="opacity:1" fill="#edde86" d="M360.5 152.5c-.167 10.672 0 21.339.5 32 .708.881 1.542 1.547 2.5 2a710.977 710.977 0 0 1 21.5 23c.5 35.332.667 70.665.5 106h-21c.167-32.335 0-64.668-.5-97l-24-28c5.854-51.123-9.646-94.29-46.5-129.5-49.727-37.904-102.394-42.237-158-13C98.062 71.145 76.229 104.645 70 148.5l-1 41a458.237 458.237 0 0 1-24 27c-.5 32.998-.667 65.998-.5 99h-20c-.32-36.043.013-72.043 1-108a204.28 204.28 0 0 1 22-25c-3.56-53.679 14.108-98.846 53-135.5 48.563-39.275 102.563-48.942 162-29 58.918 25.658 91.585 70.491 98 134.5Z"/>
        <path style="opacity:1" fill="#f1e39d" d="M385.5 315.5c-112.165 1-224.499 1.333-337 1-1.068-.934-2.401-1.268-4-1-.167-33.002 0-66.002.5-99a458.237 458.237 0 0 0 24-27l1-41c6.229-43.855 28.062-77.355 65.5-100.5 55.606-29.237 108.273-24.904 158 13 36.854 35.21 52.354 78.377 46.5 129.5l24 28c.5 32.332.667 64.665.5 97h21Z"/>
        <path style="opacity:1" fill="#fefffe" d="M200.5 33.5c51.397.606 90.564 22.606 117.5 66 14.929 29.569 20.596 60.902 17 94a458.453 458.453 0 0 0 24 27c.5 31.665.667 63.332.5 95h-310c-.167-32.002 0-64.002.5-96a724.166 724.166 0 0 1 24-27c-5.783-50.004 9.384-92.171 45.5-126.5 23.768-18.976 50.768-29.81 81-32.5Z"/>
        <path style="opacity:1" fill="#e0c08c" d="M360.5 152.5a475.007 475.007 0 0 1 1.5 31c1.039.744 1.539 1.744 1.5 3-.958-.453-1.792-1.119-2.5-2a511.925 511.925 0 0 1-.5-32Z"/>
        <path style="opacity:1" fill="#ecd698" d="M25.5 207.5a2972.885 2972.885 0 0 0-1 108h20c1.599-.268 2.932.066 4 1h-25c-.167-36.002 0-72.002.5-108 .383-.556.883-.889 1.5-1Z"/>
        <path style="opacity:1" fill="#c78b4a" d="M12.5 316.5c128 1.333 256 1.333 384 0 1 .333 1.667 1 2 2h-388c.333-1 1-1.667 2-2Z"/>
        <path style="opacity:1" fill="#3e3e3f" d="M11.5 318.5h387l11 10v53l-12 11h-386l-12-11v-53h1c4.27-2.601 7.937-5.934 11-10Z"/>
        <path style="opacity:.957" fill="#787878" d="M10.5 318.5h1c-3.063 4.066-6.73 7.399-11 10 3-3.667 6.333-7 10-10Z"/>
        <path style="opacity:.999" fill="#d4944e" d="M11.5 392.5h386v3c-36.668-.167-73.335 0-110 .5a1376.221 1376.221 0 0 0-31 31.5h-104a1376.221 1376.221 0 0 0-31-31.5c-36.665-.5-73.332-.667-110-.5v-3Z"/>
        <path style="opacity:1" fill="#fefffe" d="m274.5 395.5-22 23c-32.002.167-64.002 0-96-.5l-22-22c46.665-.5 93.332-.667 140-.5Z"/>
        <path style="opacity:1" fill="#d8a26a" d="M274.5 395.5a2.428 2.428 0 0 1 2 .5 252.747 252.747 0 0 1-24 22.5l22-23Z"/>
    </g>

    <!-- Number (same canvas coordinates as Armor) -->
    <text
        x="240" y="240"
        text-anchor="middle"
        dominant-baseline="middle"
        font-size="{{ $numberSize }}"
        font-weight="900"
        font-family="inherit"
        fill="#000">
        {{ $number }}
    </text>

    <!-- Label (same baseline as Armor’s dark bar) -->
    <text
        x="240" y="392"
        text-anchor="middle"
        dominant-baseline="middle"
        font-size="{{ $labelSize }}"
        font-weight="900"
        font-family="inherit"
        fill="{{ $labelColor }}"
        style="letter-spacing:1.2px; text-transform:uppercase;">
        {{ $label }}
    </text>
</svg>
