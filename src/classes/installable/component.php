<?php

/**
 * Component installable class.
 *
 * @package WordPoints
 * @since   2.4.0
 */

/**
 * Base installable bootstrap for a component.
 *
 * @since 2.4.0
 */
abstract class WordPoints_Installable_Component extends WordPoints_Installable {

	/**
	 * @since 2.4.0
	 */
	protected $type = 'component';

	/**
	 * @since 2.4.0
	 */
	public function get_version() {

		$component = WordPoints_Components::instance()->get_component(
			$this->slug
		);

		return $component['version'];
	}
}

// EOF
