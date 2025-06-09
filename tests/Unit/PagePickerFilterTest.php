<?php

use Basics09\PagePickerFilter;
use Kirby\Cms\App as Kirby;
use Kirby\Cms\Collection;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;

beforeEach(function () {
	// Ensure clean state before each test
	if (Kirby::instance(null, true) !== null) {
		Kirby::destroy();
	}

	// Create a fresh Kirby instance for each test
	$this->kirby = new Kirby([
		'roots' => [
			'index' => '/tmp'
		],
		'site' => [
			'children' => [
				[
					'slug' => 'home',
					'template' => 'home',
					'content' => [
						'title' => 'Home'
					]
				],
				[
					'slug' => 'about',
					'template' => 'default',
					'status' => 'published',
					'content' => [
						'title' => 'About',
					],
					'children' => [
						[
							'slug' => 'team',
							'template' => 'default',
							'status' => 'published',
							'content' => [
								'title' => 'Team',
							]
						],
						[
							'slug' => 'history',
							'template' => 'default',
							'status' => 'draft',
							'content' => [
								'title' => 'History',
							]
						],
						[
							'slug' => 'blibb',
							'template' => 'blibb',
							'status' => 'draft',
							'content' => [
								'title' => 'Blibb',
							]
						]
					]
				],
				[
					'slug' => 'contact',
					'template' => 'default',
					'content' => [
						'title' => 'Contact',
						'status' => 'draft'
					]
				]
			]
		]
	]);

	$this->site = $this->kirby->site();
	$this->aboutPage = $this->site->find('about');
});

afterEach(function () {
	// Properly destroy the Kirby instance to clean up error handlers
	if (isset($this->kirby)) {
		Kirby::destroy();
		$this->kirby = null;
	}
});

test('defaults method returns correct default options', function () {
	$filter = new PagePickerFilter(['model' => $this->site]);
	$defaults = $filter->defaults();

	expect($defaults)
		->toHaveKey('filter', null)
		->toHaveKey('subpages', true)
		->toHaveKey('query', null);
});

test('filterQuery returns the filter option', function () {
	$filterString = "filterBy('intendedTemplate', 'in', ['default'])";
	$filter = new PagePickerFilter([
		'model' => $this->site,
		'filter' => $filterString
	]);

	expect($filter->filterQuery())->toBe($filterString);
});

test('filterQuery returns null when no filter is set', function () {
	$filter = new PagePickerFilter(['model' => $this->site]);

	expect($filter->filterQuery())->toBeNull();
});

test('itemsForParent returns all children when no filter is applied', function () {
	$filter = new PagePickerFilter([
		'model' => $this->aboutPage,
		'parent' => $this->aboutPage->id()
	]);
	$items = $filter->itemsForParent();

	expect($items)->toBeInstanceOf(Pages::class);
	expect($items->count())->toBe(3); // team and history
});

test('itemsForParent applies filter when filter query is provided', function () {
	$filter = new PagePickerFilter([
		'model' => $this->aboutPage,
		'parent' => $this->aboutPage->id(),
		'filter' => "filterBy('intendedTemplate', 'in', ['default'])"
	]);

	$items = $filter->itemsForParent();

	expect($items)->toBeInstanceOf(Pages::class);
	expect($items->count())->toBe(2); // both team and history have default template
});

test('applyFilterQueryToChildren filters children correctly', function () {
	$filter = new PagePickerFilter([
		'model' => $this->site,
		'filter' => "filterBy('intendedTemplate', 'in', ['default'])"
	]);

	$filteredPages = $filter->applyFilterQueryToChildren($this->aboutPage);

	expect($filteredPages)->toBeInstanceOf(Pages::class);
	expect($filteredPages->count())->toBe(2); // both team and history have default template
});

test('applyFilterQueryToChildren works with site as parent', function () {
	$filter = new PagePickerFilter([
		'model' => $this->site,
		'filter' => "filterBy('intendedTemplate', 'in', ['default'])"
	]);

	$filteredPages = $filter->applyFilterQueryToChildren($this->site);

	expect($filteredPages)->toBeInstanceOf(Pages::class);
	expect($filteredPages->count())->toBe(2); // about and contact have default template
});

test('pageHasFilteredChildren returns true when page has children matching filter', function () {
	$filter = new PagePickerFilter([
		'model' => $this->site,
		'filter' => "filterBy('intendedTemplate', 'in', ['default'])"
	]);

	$hasFilteredChildren = $filter->pageHasFilteredChildren($this->aboutPage);

	expect($hasFilteredChildren)->toBeTrue();
});

test('pageHasFilteredChildren returns false when page has no children matching filter', function () {
	$filter = new PagePickerFilter([
		'model' => $this->site,
		'filter' => "filterBy('intendedTemplate', 'in', ['nonexistent'])"
	]);

	$hasFilteredChildren = $filter->pageHasFilteredChildren($this->aboutPage);

	expect($hasFilteredChildren)->toBeFalse(); // no children match nonexistent template
});

test('pageHasFilteredChildren returns false when page has no children', function () {
	$filter = new PagePickerFilter([
		'model' => $this->site,
		'filter' => "filterBy('intendedTemplate', 'in', ['default'])"
	]);

	$teamPage = $this->aboutPage->find('team');
	$hasFilteredChildren = $filter->pageHasFilteredChildren($teamPage);

	expect($hasFilteredChildren)->toBeFalse();
});

test('itemsToArray modifies hasChildren property based on filtered children', function () {
	$filter = new PagePickerFilter([
		'model' => $this->site,
		'filter' => "filterBy('intendedTemplate', 'in', ['default'])"
	]);

	// Create a collection with the about page
	$items = new Collection([$this->aboutPage]);
	$result = $filter->itemsToArray($items);

	expect($result)->toBeArray();
	expect($result[0])->toHaveKey('hasChildren');
	expect($result[0]['hasChildren'])->toBeTrue(); // about page has children with default template
});

test('itemsToArray returns parent result when items is null', function () {
	$filter = new PagePickerFilter(['model' => $this->site]);

	$result = $filter->itemsToArray(null);

	expect($result)->toBeArray();
});

test('itemsToArray handles empty collection', function () {
	$filter = new PagePickerFilter(['model' => $this->site]);
	$emptyCollection = new Collection([]);

	$result = $filter->itemsToArray($emptyCollection);

	expect($result)->toBeArray();
});

test('filter with complex query works correctly', function () {
	$filter = new PagePickerFilter([
		'model' => $this->site,
		'filter' => "filterBy('intendedTemplate', 'in', ['default'])"
	]);

	$filteredPages = $filter->applyFilterQueryToChildren($this->site);

	expect($filteredPages)->toBeInstanceOf(Pages::class);
	expect($filteredPages->count())->toBe(2); // about and contact have default template
});

test('constructor accepts all required parameters', function () {
	$params = [
		'model' => $this->site,
		'filter' => "filterBy('intendedTemplate', 'in', ['default'])",
		'subpages' => false,
		'query' => 'some query'
	];

	$filter = new PagePickerFilter($params);

	expect($filter->filterQuery())->toBe("filterBy('intendedTemplate', 'in', ['default'])");
});

test('applyFilterQueryToChildren returns null when filter query is invalid', function () {
	$filter = new PagePickerFilter([
		'model' => $this->site,
		'filter' => 'invalidMethod()'
	]);

	$filteredPages = $filter->applyFilterQueryToChildren($this->aboutPage);

	// This test expects that invalid queries return an empty Pages collection
	expect($filteredPages)->toBeInstanceOf(Pages::class);
	expect($filteredPages->count())->toBe(0);
});

test('works with different content field types', function () {
	$filter = new PagePickerFilter([
		'model' => $this->site,
		'filter' => "filterBy('title', '==', 'About')"
	]);

	$filteredPages = $filter->applyFilterQueryToChildren($this->site);

	expect($filteredPages)->toBeInstanceOf(Pages::class);
	expect($filteredPages->count())->toBe(1);
	expect($filteredPages->first()->slug())->toBe('about');
});

test('handles filter with multiple conditions', function () {
	$filter = new PagePickerFilter([
		'model' => $this->site,
		'filter' => "filterBy('intendedTemplate', '==', 'default').filterBy('title', '!=', 'Contact')"
	]);

	$filteredPages = $filter->applyFilterQueryToChildren($this->site);

	expect($filteredPages)->toBeInstanceOf(Pages::class);
	expect($filteredPages->count())->toBe(1); // should match about but not contact
	expect($filteredPages->first()->slug())->toBe('about');
});

test('works with sorting in filter query', function () {
	$filter = new PagePickerFilter([
		'model' => $this->site,
		'filter' => "filterBy('intendedTemplate', '==', 'default').sortBy('title', 'desc')"
	]);

	$filteredPages = $filter->applyFilterQueryToChildren($this->site);

	expect($filteredPages)->toBeInstanceOf(Pages::class);
	expect($filteredPages->count())->toBe(2);
	expect($filteredPages->first()->slug())->toBe('contact'); // Contact comes before About when sorted desc
});

test('handles empty filter gracefully', function () {
	$filter = new PagePickerFilter([
		'model' => $this->aboutPage,
		'parent' => $this->aboutPage->id(),
		'filter' => ''
	]);

	$items = $filter->itemsForParent();

	expect($items)->toBeInstanceOf(Pages::class);
	expect($items->count())->toBe(3); // should return all children when filter is empty
});

test('toArray method works correctly', function () {
	$filter = new PagePickerFilter([
		'model' => $this->site,
		'filter' => "filterBy('intendedTemplate', '==', 'default')"
	]);

	$result = $filter->toArray();

	expect($result)->toBeArray();
	expect($result)->toHaveKey('data');
	expect($result['data'])->toBeArray();
});
