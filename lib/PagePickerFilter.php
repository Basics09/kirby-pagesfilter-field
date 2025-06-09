<?php

namespace Basics09;

use Kirby\Cms\Collection;
use Kirby\Cms\Page;
use Kirby\Cms\PagePicker;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Query\Query;

class PagePickerFilter extends PagePicker
{
	/**
	 * @return string[]
	 */
	public function defaults(): array
	{
		return [
			...parent::defaults(),
			'filter' => null,
			'subpages' => true,
			'query' => null,
		];
	}

	public function itemsForParent(): Pages
	{
		return $this->filterQuery()
			? $this->applyFilterQueryToChildren($this->parent())
			: $this->parent()->children();
	}

	public function applyFilterQueryToChildren(Page|Site $parent): Pages
	{
		$query = Query::factory("children.{$this->filterQuery()}");
		$result = $query->resolve($parent);

		// Ensure we always return a Pages object, even if empty
		return $result instanceof Pages ? $result : new Pages();
	}

	public function filterQuery(): ?string
	{
		return $this->options['filter'];
	}

	/**
	 * Converts all given items to an associative
	 * array that is already optimized for the
	 * panel picker component.
	 *
	 *
	 * @param \Kirby\Cms\Collection<\Kirby\Cms\Page> $items
	 * @return array<int, array{id: string, image: string, info: string, layout: string, model: string, text: string, hasChildren: bool}>
	 */
	public function itemsToArray(?Collection $items = null): array
	{
		$result = parent::itemsToArray($items);

		if (! $items) {
			return $result;
		}

		/**
		 * Kirby's PagePicker uses Page::panel()->pickerData() which sets `hasChildren` to true
		 * if the page has any children. This can be misleading when a filter is applied,
		 * as the page might have children, but none that match the filter.
		 *
		 * To accurately reflect whether a page has children that match the filter,
		 * we need to check if the page has any children that pass the filter query.
		 * If no children match the filter, `hasChildren` is set to false.
		 */
		foreach ($result as &$item) {
			$pageId = $item['id'];
			$page = $items->findByKey($pageId);

			$item['hasChildren'] = $this->pageHasFilteredChildren($page);
		}

		return $result;
	}

	/**
	 * Checks if a page has filtered children by applying the filter query to its children.
	 */
	public function pageHasFilteredChildren(?Page $page): bool
	{
		return $page?->hasChildren()
			? $this->applyFilterQueryToChildren($page)->isNotEmpty()
			: false;
	}
}
