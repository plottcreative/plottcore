#!/bin/bash

# Define file paths
json_file="./acf-json/group_623bb7b816840.json"
php_file="./templates/parts/acf-fields.php"
temp_php_file="./templates/parts/acf-fields-temp.php"
blocks_dir="./templates/blocks"

# Extract unique layouts
unique_layouts=$(jq -r '.fields[0].layouts | to_entries[] | .value.name' $json_file | sort | uniq)

# Convert unique layouts to an array with hyphenated names
unique_layouts_hyphenated=()
for layout_name in $unique_layouts; do
    unique_layouts_hyphenated+=($(echo "$layout_name" | tr '_' '-'))
done

# Get a list of existing layout directories
existing_dirs=$(find "$blocks_dir" -maxdepth 1 -mindepth 1 -type d -exec basename {} \;)

# Start writing to the temporary PHP file
cat > $temp_php_file <<EOL
    <?php

    \$template = \PLOTT_THEME\Inc\Load_Template::get_instance();

    // ID of the current item in the WordPress Loop
    \$id = get_the_ID();

    // Loop through layouts
    if( have_rows('page_builder') ):
        while ( have_rows('page_builder') ) : the_row();
            switch ( get_row_layout() ) {
EOL

# Loop through each unique layout and generate cases
IFS=$'\n'
for layout_name in $unique_layouts; do
    layout_name_hyphenated=$(echo "$layout_name" | tr '_' '-')
    layout_name_nospecial=$(echo "$layout_name" | tr '_' '')

    layout_dir="$blocks_dir/$layout_name_hyphenated"

    if [ -d "$layout_dir" ]; then
        echo "Directory $layout_dir already exists. Skipping..."
    else
        mkdir -p "$layout_dir"

        php_layout_file="$layout_dir/$layout_name_hyphenated.php"
        scss_layout_file="$layout_dir/_$layout_name_hyphenated.scss"
        js_layout_file="$layout_dir/$layout_name_hyphenated.js"

        # Create PHP file in the layout directory
        cat > $php_layout_file <<EOL
<section class="$layout_name_hyphenated">
    <div class="${layout_name_hyphenated}__container container">
        <div class="${layout_name_hyphenated}__row row">
        </div>
    </div>
</section>
EOL

        # Create SCSS file in the layout directory
        cat > $scss_layout_file <<EOL
.$layout_name_hyphenated {
    &__container {
    }
    &__row {
    }
}
EOL

        # Create JS file in the layout directory
        cat > $js_layout_file <<EOL
// JavaScript for $layout_name_hyphenated
export function $layout_name_nospecial() {
    // Your code here
}
EOL
    fi

    echo "                case '$layout_name':" >> $temp_php_file
    echo "                    {" >> $temp_php_file
    echo "                        \$template->render( 'templates/blocks/$layout_name_hyphenated/$layout_name_hyphenated'," >> $temp_php_file
    echo "                            [" >> $temp_php_file

    # Extract subfields for this layout
    subfields=$(jq -r --arg layout_name "$layout_name" '.fields[0].layouts | to_entries[] | select(.value.name == $layout_name) | .value.sub_fields[]?.name' $json_file)

    # Loop through each subfield and add it to the case
    IFS=$'\n'
    for sub_field_name in $subfields; do
        echo "                                '$sub_field_name' => get_sub_field( '$sub_field_name' )," >> $temp_php_file
    done

    echo "                            ]" >> $temp_php_file
    echo "                        );" >> $temp_php_file
    echo "                    }" >> $temp_php_file
    echo "                    break;" >> $temp_php_file
done

# End the PHP switch statement and the main loop
cat >> $temp_php_file <<EOL
                default:
                    // Add a default case if needed
                    break;
            }
        endwhile;
    else :
        // No layouts found
    endif;
    ?>
EOL

# Replace the original PHP file with the temporary file
mv $temp_php_file $php_file

# Remove directories that do not exist in the JSON file
for dir in $existing_dirs; do
    if [[ ! " ${unique_layouts_hyphenated[@]} " =~ " ${dir} " ]]; then
        echo "Removing directory $blocks_dir/$dir"
        rm -rf "$blocks_dir/$dir"
    fi
done

echo "page-builder.php has been updated with new layouts and corresponding files."
