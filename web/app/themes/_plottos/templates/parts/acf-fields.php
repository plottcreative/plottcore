    <?php

    $template = \PLOTT_THEME\Inc\Load_Template::get_instance();

    // ID of the current item in the WordPress Loop
    $id = get_the_ID();

    // Loop through layouts
    if( have_rows('page_builder') ):
        while ( have_rows('page_builder') ) : the_row();
            switch ( get_row_layout() ) {
                case 'accordion_001':
                    {
                        $template->render( 'templates/blocks/accordion-001/accordion-001',
                            [
                                'items' => get_sub_field( 'items' ),
                            ]
                        );
                    }
                    break;
                case 'cta_001':
                    {
                        $template->render( 'templates/blocks/cta-001/cta-001',
                            [
                            ]
                        );
                    }
                    break;
                case 'faqs_001':
                    {
                        $template->render( 'templates/blocks/faqs-001/faqs-001',
                            [
                                'faqs' => get_sub_field( 'faqs' ),
                            ]
                        );
                    }
                    break;
                case 'heading_content_001':
                    {
                        $template->render( 'templates/blocks/heading-content-001/heading-content-001',
                            [
                                'heading' => get_sub_field( 'heading' ),
                                'content' => get_sub_field( 'content' ),
                            ]
                        );
                    }
                    break;
                case 'hero_001':
                    {
                        $template->render( 'templates/blocks/hero-001/hero-001',
                            [
                            ]
                        );
                    }
                    break;
                case 'hero_002':
                    {
                        $template->render( 'templates/blocks/hero-002/hero-002',
                            [
                            ]
                        );
                    }
                    break;
                case 'hero_003':
                    {
                        $template->render( 'templates/blocks/hero-003/hero-003',
                            [
                            ]
                        );
                    }
                    break;
                case 'hero_004':
                    {
                        $template->render( 'templates/blocks/hero-004/hero-004',
                            [
                            ]
                        );
                    }
                    break;
                case 'image_content_001':
                    {
                        $template->render( 'templates/blocks/image-content-001/image-content-001',
                            [
                                'image_position' => get_sub_field( 'image_position' ),
                                'image' => get_sub_field( 'image' ),
                                'heading' => get_sub_field( 'heading' ),
                                'content' => get_sub_field( 'content' ),
                                'page_link' => get_sub_field( 'page_link' ),
                            ]
                        );
                    }
                    break;
                case 'logo_slider_001':
                    {
                        $template->render( 'templates/blocks/logo-slider-001/logo-slider-001',
                            [
                                'logos' => get_sub_field( 'logos' ),
                            ]
                        );
                    }
                    break;
                default:
                    // Add a default case if needed
                    break;
            }
        endwhile;
    else :
        // No layouts found
    endif;
    ?>
