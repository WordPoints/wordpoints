/**
 * Initialize a WordPoints points logs table as a dataTable.
 *
 * @package WordPoints
 * @since 1.0.0
 */

/** @global */
WordPointsDataTable;

jQuery( document ).ready( function () {

	jQuery( WordPointsDataTable.selector ).dataTable( WordPointsDataTable.args );
});
