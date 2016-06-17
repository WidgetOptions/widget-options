<?php

//remove widgetopts_pages transient when new page created
if( !function_exists( 'widgetopts_delete_transient_pages' ) ){
	add_action( 'transition_post_status', 'widgetopts_delete_transient_pages', 10, 3 );
	function widgetopts_delete_transient_pages( $new_status, $old_status, $post  ){
		if ( 'publish' == $new_status && 'publish' != $old_status && 'page' == $post->post_type ){
	        delete_transient( 'widgetopts_pages' );
	    }
	}
}

//remove widgetopts_categories transient when new category created
if( !function_exists( 'widgetopts_delete_transient_terms' ) ){
	add_action( 'create_term', 'widgetopts_delete_transient_terms', 10, 3 );
	function widgetopts_delete_transient_terms( $term_id, $tt_id, $taxonomy ){
		if( $taxonomy == 'category' ){
			delete_transient( 'widgetopts_categories' );
		}
	}
}
?>