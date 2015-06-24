<?php

/**
 * Display the ranks administration screen.
 *
 * @package WordPoints\Ranks
 * @since 1.7.0
 */

$rank_groups = WordPoints_Rank_Groups::get();

if ( empty( $rank_groups ) ) {

	?>
	<div class="wrap">
		<?php wordpoints_show_admin_error( esc_html__( 'No rank groups are currently available.', 'wordpoints' ) ); ?>
	</div>
	<?php

	return;
}

$rank_group = $rank_groups[ wordpoints_admin_get_current_tab( $rank_groups ) ];

?>

<div class="wrap">
	<h2><?php esc_html_e( 'Ranks', 'wordpoints' ); ?></h2>

	<?php wordpoints_admin_show_tabs( wp_list_pluck( $rank_groups, 'name' ), false ) ?>

	<div id="message" class="error" style="display: none;">
		<p></p>
	</div>

	<?php

	/**
	 * Display content at the top of the Ranks admin screen.
	 *
	 * @since 1.7.0
	 *
	 * @param WordPoints_Rank_Group $rank_group The rank group being displayed.
	 */
	do_action( 'wordpoints_ranks_admin_screen_top', $rank_group );

	?>

	<div class="wordpoints-rank-group-container">
		<p class="description group-description"><?php echo esc_html( $rank_group->description ); ?></p>
		<ul
			id="wordpoints-rank-group_<?php echo esc_attr( $rank_group->slug ); ?>"
			class="wordpoints-rank-group"
			data-slug="<?php echo esc_attr( $rank_group->slug ); ?>"
			data-nonce="<?php echo esc_attr( wp_create_nonce( "wordpoints_get_ranks-{$rank_group->slug}" ) ); ?>"
		>
		</ul>
		<div class="spinner-overlay" style="display: block;">
			<span class="spinner is-active"></span>
		</div>
		<div class="controls">
			<button class="add-rank button-primary"><?php esc_html_e( 'Add Rank', 'wordpoints' ); ?></button>
			<select class="wordpoints-rank-types">
				<option><?php esc_html_e( 'Select Type', 'wordpoints' ); ?></option>

				<?php foreach ( $rank_group->get_types() as $rank_type ) : ?>
					<?php if ( 'base' !== $rank_type ) : ?>
						<option value="<?php echo esc_attr( $rank_type ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( "wordpoints_create_rank|{$rank_group->slug}|{$rank_type}" ) ); ?>">
							<?php echo esc_html( WordPoints_Rank_Types::get_type( $rank_type )->get_name() ); ?>
						</option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	<?php

	/**
	 * Display content at the bottom of the Ranks admin screen.
	 *
	 * @since 1.7.0
	 *
	 * @param WordPoints_Rank_Group $rank_group The rank group being displayed.
	 */
	do_action( 'wordpoints_ranks_admin_screen_bottom', $rank_group );

	?>
</div>

<?php foreach ( $rank_group->get_types() as $rank_type ) : ?>
	<script type="text/template" class="rank-template_<?php echo sanitize_html_class( $rank_type ); ?>">
		<div class="view">
			<div>
				<%- name %>
				<?php if ( ($field = key( wp_list_filter( WordPoints_Rank_Types::get_type( $rank_type )->get_meta_fields(), array( 'in_title' => true ) ) )) ) : ?>
					<span><% if ( typeof <?php echo preg_replace( '/[^a-z0-9_]/i', '', $field ); // WPCS: XSS OK ?> !== "undefined" ) { print( <?php echo preg_replace( '/[^a-z0-9_]/i', '', $field ); // WPCS: XSS OK ?> ); } %></span>
				<?php endif; ?>
			</div>
			<a class="edit"><?php echo esc_html_x( 'Edit', 'rank', 'wordpoints' ); ?></a>
			<a class="close"><?php esc_html_e( 'Close', 'wordpoints' ); ?></a>
		</div>
		<form>
			<div class="fields">
				<p class="description description-thin">
				<label>
					<?php echo esc_html_x( 'Title', 'rank', 'wordpoints' ); ?>
					<input class="widefat" type="text" value="<%- name %>" name="name" />
				</label>
				</p>
				<input type="hidden" name="type" value="<?php echo esc_attr( $rank_type ); ?>" />
				<input type="hidden" name="order" value="<%- order %>" />
				<input type="hidden" name="nonce" value="<%- nonce %>" />
				<?php WordPoints_Rank_Types::get_type( $rank_type )->display_rank_meta_form_fields( array(), array( 'placeholders' => true ) ); ?>
			</div>
			<div class="messages">
				<div class="success"></div>
				<div class="err"></div>
			</div>
			<div class="actions">
				<div class="spinner-overlay">
					<span class="spinner is-active"></span>
				</div>
				<div class="action-buttons">
					<button class="save button-primary" disabled><?php esc_html_e( 'Save', 'wordpoints' ); ?></button>
					<button class="cancel button-secondary"><?php esc_html_e( 'Cancel', 'wordpoints' ); ?></button>
					<button class="close button-secondary"><?php esc_html_e( 'Close', 'wordpoints' ); ?></button>
					<?php if ( 'base' !== $rank_type ) : ?>
						<button class="delete button-secondary"><?php esc_html_e( 'Delete', 'wordpoints' ); ?></button>
					<?php endif; ?>
				</div>
			</div>
		</form>
	</script>
<?php endforeach; ?>
