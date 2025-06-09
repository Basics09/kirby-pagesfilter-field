<?php

use Basics09\PagePickerFilter;
use Kirby\Cms\App as Kirby;

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('kirby/pagesfilter', [
	'fields' => [
		'pagesfilter' => [
			'extends' => 'pages',
			'props' => [
				'filter' => fn (?string $filter = null): ?string => $filter,

				'query' => fn (): ?string => null,
				'subpages' => fn (): bool => true,
			],
			'methods' => [
				'pagepicker' => function (array $params = []): array {
					$params['model'] = $this->model();
					$params['filter'] = $this->filter();

					return (new PagePickerFilter($params))->toArray();
				},
			],
		],
	],
]);
