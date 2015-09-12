<?php

get_header();

$show = get_queried_object();
$show_id = $show->term_id;

$all_options = wp_load_alloptions();
$options = array();
foreach ($all_options as $key => $value) {
    $key_start = 'show_' . $show_id . '_custom_option_';
    if (preg_match('/^' . $key_start . '\w*$/', $key)) {
        $options[str_replace($key_start, '', $key)] = $value;
    }
}

$description = $show->description;
$name = $show->name;
$fullname = $show->name;
$name_prelude = $options['name_prelude'];
$fb_link = $options['fb_link'];
$tw_link = $options['tw_link'];
$ended = $options['ended'];
$hidden = $options['hidden'];
$category = $options['show_category'];
$slots = unserialize($options['slot']);
$slug = $show->slug;

$category_slug = strtolower($category);
$category_slug = str_replace(' ', '-', $category_slug);

if ($name_prelude !== "") {
    $name = str_replace($name_prelude, '', $name);
}

$slot_strings = array();

foreach ($slots as $slot) {
    $slot_strings[] = $slot['day'] . 's from ' . $slot['from'];
}

$term_objects = get_objects_in_term($show_id, 'shows');

$users = array();
foreach ($term_objects as $object_id) {
    $userObject = get_user_by('id', $object_id);
    $postObject = get_post($object_id);
    if ($userObject) {
        $user_id = $userObject->data->ID;
        $user = array();
        $user['name'] = $userObject->data->display_name;
        $user['link'] = get_author_posts_url($user_id);
        $user['image'] = get_avatar($id, 32);

        $user['committee_role'] = esc_attr(get_the_author_meta('committee_role', $user_id));

        $users[] = $user;
    }
}

$postObjects = get_posts(array(
    'tax_query' => array(
        array(
            'taxonomy' => 'shows',
            'field' => 'term_id',
            'terms' => $show_id
        )
    )
));

$posts = array();
foreach ($postObjects as $postObject) {
    $post = array();
    $post['title'] = $postObject->post_title;
    $post['date'] = $postObject->post_date_gmt;
    $post['excerpt'] = $postObject->post_excerpt;
    $post['link'] = get_permalink($postObject->ID);
    $posts[] = $post;
}
?>

<header class="show-page-header <?php echo $category_slug; ?>">
    <div class="titles">
        <h2 class="title-prelude"><?php echo $name_prelude; ?></h2>
        <h1 class="title"><?php echo $name; ?></h1>
        <h3 class="time"><?php echo implode(', ', $slot_strings) ?></h3>
    </div>
</header>

<div class="main-content">
    <div class="show-page-show-info">
        <div class="show-page-members">
            <h1>Show Hosts</h1>
            <ul>
                <?php
                    if (count($users) < 1) {
                        echo 'This show has no hosts assigned.';
                    }
                    else {
                        foreach ($users as $user) {
                            $role = $user['committee_role'];

                            echo '<li class="host">';
                            echo '<a href="' . $user['link'] . '">';
                            echo $user['image'];
                            echo '<span class="name">' . $user['name'] . '</span>';
                            echo '</a>';

                            if ($role !== '') {
                                echo '<a href="/committee" title="Committee Member" class="committee-tag">' . $role . '</a>';
                            }

                            echo '</li>';
                        }
                    }

                ?>
            </ul>
        </div>
        <p class="show-page-description"><?php echo $description; ?></p>
        <ul class="show-page-external-links">
            <li><a href="<?php echo $fb_link; ?>" class="facebook">Facebook</a></li>
            <li><a href="<?php echo $tw_link; ?>" class="twitter">Twitter</a></li>
        </ul>

    </div>
    <h1>Recent shows</h1>
    <div class="show-page-posts">
        <?php
            if (count($posts) < 1) {
                echo 'This show has made no posts.';
            }
            foreach ($posts as $post) {
                echo '<article class="show-page-post">';
                echo '<header><a href="' . $post['link'] . '">' . $post['title'] . '</a></header>';
                echo '<div class="body">' . $post['excerpt'] . '</div>';
                echo '<footer>' . $post['date'] .'</footer>';
                echo '</article>';
            }
        ?>
    </div>
</div>

<?php get_footer(); ?>
