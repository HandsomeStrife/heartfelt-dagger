{{-- resources/views/components/state-frame.blade.php --}}
@props([
    'number' => '+1',
    'label' => null,
    'labelBg' => '#000000',
    'labelColor' => '#ffffff',
    'numberSize' => 140,  // main number size
    'symbolSize' => 90,   // smaller + / - sign
    'labelSize' => 42,    // label size
])

<svg
    {{ $attributes->merge([
        'class' => 'inline-block align-middle',
        'role' => 'img',
        'aria-label' => trim(($label ? $label.' ' : '').'State frame '.$number),
    ]) }}
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 360 380"
    fill="none"
>
  <defs>
    <linearGradient id="goldBase" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%"  stop-color="#FFD65A"/>
      <stop offset="35%" stop-color="#FFC23A"/>
      <stop offset="65%" stop-color="#E39B1D"/>
      <stop offset="100%" stop-color="#B87512"/>
    </linearGradient>
    <linearGradient id="goldHighlight" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%"  stop-color="#FFF6C8"/>
      <stop offset="40%" stop-color="#FFE682"/>
      <stop offset="100%" stop-color="#D58F1A"/>
    </linearGradient>
  </defs>

  <!-- Shield -->
  <polygon id="shield"
           points="40,24 320,24 320,222 292,248 292,300 180,352 68,300 68,248 40,222"
           fill="white"/>
  <use href="#shield" fill="none" stroke="url(#goldBase)" stroke-width="28" stroke-linejoin="round"/>
  <use href="#shield" fill="white" stroke="url(#goldHighlight)" stroke-width="14" stroke-linejoin="round"/>

  <!-- Combined number + symbol -->
  <text
      x="165"
      y="200"
      text-anchor="middle"
      dominant-baseline="middle"
      font-weight="900"
      font-family="inherit"
      fill="#000">
      <tspan font-size="{{ $symbolSize }}">{{ mb_substr($number, 0, 1) }}</tspan>
      <tspan font-size="{{ $numberSize }}">{{ mb_substr($number, 1) }}</tspan>
  </text>

  <!-- Label on top -->
  @if($label)
    <g aria-hidden="true">
      <rect x="15" y="0" width="330" height="60" rx="10" fill="{{ $labelBg }}" />
      <text x="180" y="34"
            text-anchor="middle"
            dominant-baseline="middle"
            font-size="{{ $labelSize }}"
            font-weight="900"
            font-family="inherit"
            fill="{{ $labelColor }}"
            style="letter-spacing:0.6px; text-transform:uppercase;">
        {{ $label }}
      </text>
    </g>
  @endif
</svg>
