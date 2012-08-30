<?php
/*
Plugin Name: IT News Widget
Plugin URI: http://www.web-news.pl/
Description: A widget to display IT news (about android, smartphones, etc.) feeds on your blog.
Version: 1.0.0
Author: Łukasz Wójcicki
Author URI: http://www.web-news.pl/
License: GPL2
*/

/*  Copyright 2012 Łukasz Wójcicki (email: lukasz@web-news.pl)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once( ABSPATH . WPINC . '/feed.php' );

/**
 * ITNewsWidget_Widget Class
 */
class ITNewsWidget_Widget extends WP_Widget
{
	/** Constructor */
	function ITNewsWidget_Widget()
	{
		parent::WP_Widget(
			'itnewswidget',
			__( 'IT News Widget', 'itnewswidget' ),
			array(	'classname' => 'itnewswidget', 'description' => __( 'Display IT news (about android, smartphones, etc.) feeds', 'itnewswidget' ) )
		);
	}

	/** @see WP_Widget::form */
	function form( $instance )
	{
		$title = esc_attr( $instance[ 'title' ] );
		if( empty( $title ) )
			$title = __( 'IT News', 'itnewswidget' );

        			
		
		$maxDisplayedItemsInTotal = $instance[ 'maxDisplayedItemsInTotal' ];
		if( !isset( $maxDisplayedItemsInTotal ) || $maxDisplayedItemsInTotal <= 0 || $maxDisplayedItemsInTotal > 100 )
			$maxDisplayedItemsInTotal = 5;

		
		?>
		<p>
			<label for="<?php echo( $this->get_field_id( 'title' ) ); ?>">
				<?php _e( 'Title:', 'itnewswidget' ); ?>
				<input class="widefat" id="<?php echo( $this->get_field_id( 'title' ) ); ?>" name="<?php echo( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo( $title ); ?>" />
			</label>
		</p>

		<p>
			<label for="<?php echo( $this->get_field_id( 'maxDisplayedItemsInTotal' ) ); ?>">
				<?php _e( 'Maximum displayed news:', 'itnewswidget' ); ?>
				<input class="widefat" id="<?php echo( $this->get_field_id( 'maxDisplayedItemsInTotal' ) ); ?>" name="<?php echo( $this->get_field_name( 'maxDisplayedItemsInTotal' ) ); ?>" type="text" value="<?php echo( $maxDisplayedItemsInTotal ); ?>" />
			</label>
		</p>

		

		<?php
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance )
	{
		// processes widget options to be saved
		$instance = $old_instance;

		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
	
		$instance[ 'maxDisplayedItemsInTotal' ] = $new_instance[ 'maxDisplayedItemsInTotal' ];
		if(    !is_numeric( $instance[ 'maxDisplayedItemsInTotal' ] )
			|| $instance[ 'maxDisplayedItemsInTotal' ] <= 0
			|| $instance[ 'maxDisplayedItemsInTotal' ] > 100 )
		{
			$instance[ 'maxDisplayedItemsInTotal' ] = 5;
		}

		
		return $instance;

	}

	static function sort( $a, $b )
	{
		if( $a->get_date( 'U' ) < $b->get_date( 'U' ) )
		{
			return 1;
		}

		if( $a->get_date( 'U' ) > $b->get_date( 'U' ) )
		{
			return -1;
		}
		
		return 0;
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance )
	{
		// outputs the content of the widget
		extract( $args );

		echo( $before_widget );

		// Get title
		$title = apply_filters( 'widget_title', $instance[ 'title' ] );
		echo( $before_title . $title . $after_title );

		$maxDisplayedItemsInTotal = $instance[ 'maxDisplayedItemsInTotal' ];
		if( !isset( $maxDisplayedItemsInTotal ) || $maxDisplayedItemsInTotal <= 0 || $maxDisplayedItemsInTotal > 100 )
			$maxDisplayedItemsInTotal = 5;

		
		// Get and display RSS feeds
		$rss = "http://www.web-news.pl/feed" ;
		
		$displayedItems = array();

        $feed = fetch_feed( $rss );
        if( !is_wp_error( $feed ) )
        {
            // OK
            $maxItems = $feed->get_item_quantity( $maxDisplayedItemsInTotal );
            if( $maxItems > 0 )
            {
                // OK, found items in the fetched feed
                $feedItems = $feed->get_items( 0, $maxItems );
                foreach( $feedItems as $item )
                {
                    $displayedItems[] = $item;
                }
            }
        }

		if( !empty( $displayedItems ) )
		{
			
			usort( $displayedItems, 'ITNewsWidget_Widget::sort' );

			echo( '<ul>' );
			$displayedItemsCount = 0;
			foreach( $displayedItems as $item )
			{
				echo( '<li><a title="'.date_i18n( get_option( 'date_format' ), $item->get_date( 'U' ) ).'" href="'.$item->get_permalink().'" rel="nofollow">'.$item->get_title().'</a>' );
				if( $source != 'hidden' )
					echo( ' <small>(<cite>'.$item->get_feed()->get_title().'</cite>)</small>' );
				echo( '</li>');
				
				$displayedItemsCount++;
				if( $displayedItemsCount > $maxDisplayedItemsInTotal )
				{
					break;
				}
			}
			echo( '</ul>' );
		}

		echo( $after_widget );
	}

} 

function ITNewsWidget_Widget_Register()
{
	load_plugin_textdomain( 'itnewsswidget', false, dirname( plugin_basename( __FILE__ ) ) );
	return register_widget( "ITNewsWidget_Widget" );
}

// register widget
add_action( 'widgets_init', 'ITNewsWidget_Widget_Register' );

