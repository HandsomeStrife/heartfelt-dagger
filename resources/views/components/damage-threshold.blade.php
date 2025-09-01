@props([
    'left' => null,
    'right' => null,
    'ink' => '#ffffff',
    'fill' => '#7c86ff',
    'font' => "ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans'",
])

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2057 300" role="img" aria-label="Damage track"
    style="shape-rendering:geometricPrecision;text-rendering:geometricPrecision;image-rendering:optimizeQuality;fill-rule:evenodd;clip-rule:evenodd;display:block;width:100%;height:auto"
    {{ $attributes }}>
    <path fill="currentColor" d="M3.5-.5h40a210.644 210.644 0 0 0 12 12.5c149.807.873 299.807 1.373 450 1.5 2.927-1.257 5.261-3.257 7-6v-1c2.386-.873 4.386-2.373 6-4.5a800.44 800.44 0 0 1 40-.5c2.034 1.048 3.367 2.714 4 5a163.719 163.719 0 0 0-1 25v5h186v-9c-.154-8.21-.154-16.21 0-24 1.696-.185 3.029-1.019 4-2.5 13.329-.5 26.663-.667 40-.5a196.468 196.468 0 0 1 12 11.5 46705.285 46705.285 0 0 0 450 1l12-12a400.25 400.25 0 0 1 40 0c.88.708 1.55 1.542 2 2.5.62 11.13 1.29 22.13 2 33 62.01.663 123.68-.004 185-2-.17-10.339 0-20.672.5-31a19.504 19.504 0 0 0 3.5-4h40c3.8 4.298 7.8 8.464 12 12.5 149.81.878 299.81 1.378 450 1.5 4.74-3.23 9.07-7.064 13-11.5 13-.5 26-.667 39-.5 1.33 1 2.67 2 4 3v40c-4.3 3.796-8.46 7.796-12.5 12a3176.62 3176.62 0 0 0 0 113 211.06 211.06 0 0 0 12.5 12v39c-1.67 1-3 2.333-4 4h-41c-3.83-3.999-7.5-8.165-11-12.5-150-.454-300-.787-450-1-3.87 4.037-7.87 7.87-12 11.5-14.05.299-28.05-.035-42-1-1.98-.817-2.98-2.317-3-4.5a95.981 95.981 0 0 0 0-27.5c-61.34-.333-122.67 0-184 1a514.862 514.862 0 0 0-1.5 31c-1.04.873-1.88 1.873-2.5 3h-41a213.03 213.03 0 0 0-12-12.5c-149.67-.454-299.336-.788-449-1a110.18 110.18 0 0 0-13 12 400.05 400.05 0 0 1-40 0l-3.5-3.5c-.5-9.994-.666-19.994-.5-30h-185c.167 10.672 0 21.339-.5 32a10.515 10.515 0 0 0-2.5 3h-42a197.79 197.79 0 0 1-11-12.5c-150.002-.456-300.002-.789-450-1a193.893 193.893 0 0 1-12 11.5 464.968 464.968 0 0 1-42-1l-2-1v-41a210.389 210.389 0 0 0 12.5-12c.667-37.333.667-74.667 0-112a210.644 210.644 0 0 0-12.5-12v-40c1.667-1 3-2.333 4-4Zm813 112-61 62c-.617-.111-1.117-.444-1.5-1a63.707 63.707 0 0 0-6.5 7 8792.782 8792.782 0 0 1-187 1 193.893 193.893 0 0 0-11.5-12c-.667-37-.667-74 0-111L560.5 46c63-.667 126-.667 189 0a6216.37 6216.37 0 0 1 67 65.5Zm747 0c-20.33 21-41 41.667-62 62-2.31 1.977-4.65 3.977-7 6a5828.526 5828.526 0 0 1-187 .5c-3.83-3.833-7.67-7.667-11.5-11.5.13-37.052.46-74.385 1-112l10.5-10.5c63.33-.667 126.67-.667 190 0a6139.938 6139.938 0 0 0 66 65.5Z" />
    <path fill="currentColor" d="M757.5 79.5a1521.703 1521.703 0 0 1 33 32.5L758 144.5a866.564 866.564 0 0 0-1.5-24 990.868 990.868 0 0 1 1-41Z"/>
    <path fill="currentColor" d="M1506.5 81.5a676.83 676.83 0 0 1 31 31 349.726 349.726 0 0 1-32 31c-.33-20.84 0-41.507 1-62Z"/>

    <!-- SECTION HEADINGS (centers adjusted) -->
    <g fill="#fff" style="font-family: {{ $font }}; font-weight: 900;">
        <text x="280"  y="100"  font-size="78" text-anchor="middle">MINOR</text>
        <text x="280"  y="165" font-size="78" text-anchor="middle">DAMAGE</text>

        <text x="1035" y="100"  font-size="78" text-anchor="middle">MAJOR</text>
        <text x="1035" y="165" font-size="78" text-anchor="middle">DAMAGE</text>

        <text x="1785" y="100"  font-size="78" text-anchor="middle">SEVERE</text>
        <text x="1785" y="165" font-size="78" text-anchor="middle">DAMAGE</text>
    </g>

    <!-- SUBCAPTIONS (aligned under each section) -->
    <g fill="{{ $ink }}" style="font-family: {{ $font }}; font-weight: 700;">
        <text x="280"  y="280" font-size="52" text-anchor="middle">Mark 1 HP</text>
        <text x="1035" y="280" font-size="52" text-anchor="middle">Mark 2 HP</text>
        <text x="1785" y="280" font-size="52" text-anchor="middle">Mark 3 HP</text>
    </g>

    <!-- NUMBERS (centers of the white arrow boxes) -->
    @if (!is_null($left))
        <text x="655" y="112.5" text-anchor="middle" dominant-baseline="central"
              style="font-family: {{ $font }}; font-weight: 900; font-size: 92px; fill: {{ $ink }};">
            {{ $left }}
        </text>
    @endif

    @if (!is_null($right))
        <text x="1400" y="112.5" text-anchor="middle" dominant-baseline="central"
              style="font-family: {{ $font }}; font-weight: 900; font-size: 92px; fill: {{ $ink }};">
            {{ $right }}
        </text>
    @endif
</svg>
