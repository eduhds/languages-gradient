<?php

function svgGradient($values, $height = 15)
{
  $svg =
    '<svg height="' .
    $height .
    '" width="100%" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="grad1" x1="0%" x2="100%" y1="0%" y2="0%">';

  $accumulator = 0;

  foreach ($values as $key => $value) {
    $accumulator += $value['percent'];

    $offset = (int) match ($key) {
      0 => 0,
      count($values) - 1 => 100,
      default => $accumulator
    };

    $svg .=
      '<stop offset="' . $offset . '%" stop-color="' . $value['color'] . '" />';
  }

  $svg .=
    '</linearGradient></defs><rect width="100%" height="' .
    $height .
    '" x="0" y="0" fill="url(#grad1)" /></svg>';

  return $svg;
}

if (isset($_GET['owner']) && isset($_GET['repo'])) {
  $owner = $_GET['owner'];
  $repo = $_GET['repo'];

  $res = file_get_contents(
    "https://api.github.com/repos/$owner/$repo/languages",
    false,
    stream_context_create([
      'http' => [
        'method' => 'GET',
        'header' =>
          'Accept: application/vnd.github.v3+json' .
          PHP_EOL .
          'User-Agent: svg-colors/1.0' .
          PHP_EOL
      ]
    ])
  );

  $repoLanguages = json_decode($res, true);

  $infoLanguages = json_decode(file_get_contents('languages.json'), true);

  $total = array_sum(array_values($repoLanguages));
  $values = [];

  foreach ($repoLanguages as $key => $value) {
    $percent = ($value / $total) * 100;
    if ($percent <= 0) {
      continue;
    }
    $values[] = [
      'percent' => $percent,
      'color' => $infoLanguages[$key]['color']
    ];
  }
} else {
  $values = [
    ['percent' => 50, 'color' => '#ff0000'],
    ['percent' => 50, 'color' => '#00ff00'],
    ['percent' => 50, 'color' => '#0000ff']
  ];
}

header('Content-Type: image/svg+xml');
echo svgGradient($values);
