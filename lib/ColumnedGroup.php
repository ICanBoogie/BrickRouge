<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge;

/**
 * A group that lay its children into columns.
 */
class ColumnedGroup extends Group
{
	/**
	 * Defines the number of columns.
	 *
	 * @var int
	 */
	const COLUMNS = '#columned-group-columns';

	/**
	 * Defines the total number of columns.
	 *
	 * @var int
	 */
	const COLUMNS_TOTAL = '#columned-group-columns-total';

	/**
	 * The instance is constructed with the following defaults:
	 *
	 * - {@link COLUMNS}: 3.
	 * - {@link COLUMNS_TOTAL}: 12.
	 *
	 * @param array $attribute
	 */
	public function __construct(array $attribute=[])
	{
		parent::__construct($attribute + [

			self::COLUMNS => 3,
			self::COLUMNS_TOTAL => 12

		]);
	}

	/**
	 * Dispatch children into columns and render them.
	 *
	 * @return Element
	 */
	protected function render_inner_html()
	{
		$columns = [];
		$children = $this->ordered_children;
		$columns_n = $this[self::COLUMNS];
		$by_column = ceil(count($children) / $columns_n);
		$i = 0;

		foreach ($children as $child_id => $child)
		{
			$column_i = $i++ % $columns_n;
			$columns[$column_i][$child_id] = $child;
		}

		$rendered_columns = [];

		foreach ($columns as $column)
		{
			$rendered_columns[] = $this->render_group_column($column);
		}

		return new Element('div', [

			Element::CHILDREN => $rendered_columns,

			'class' => "row"

		]);
	}

	/**
	 * Render a group column.
	 *
	 * @param array $column
	 *
	 * @return Element
	 */
	protected function render_group_column($column)
	{
		$w = $this[self::COLUMNS_TOTAL] / $this[self::COLUMNS];

		return new Element('div', [

			Element::INNER_HTML => $this->render_children($column),

			'class' => "col-md-$w span-$w"

		]);
	}
}