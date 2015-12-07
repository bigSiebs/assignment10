<!-- ######################     Main Navigation   ########################## -->
<nav>
    <ul class="header-subnav">
        <?php
        // This sets the current page to not be a link. Repeat this if block for
        //  each menu item


        print '<li>';
        print '<a href="' . $path . 'index.php"';
        if ($path_parts['filename'] == "index") {
            print ' class="is-active"';
        }
        print '>Home</a></li>';

        print '<li>';
        print '<a href="' . $path . 'list.php"';
        if ($path_parts['filename'] == "list") {
            print ' class="is-active"';
        }
        print '>The List</a></li>';

        print '<li>';
        print '<a href="' . $path . 'form.php"';
        if ($path_parts['filename'] == "form") {
            print ' class="is-active"';
        }
        print '>Add an Activity</a></li>';

        print '<li>';
        print '<a href="' . $path . 'about.php"';
        if ($path_parts['filename'] == "about") {
            print ' class="is-active"';
        }
        print '>About</a></li>';

        if (adminCheck($thisDatabaseReader, $username)) {
            print '<li>';
            print '<a href="' . $adminPath . 'approve.php"';
            if ($path_parts['filename'] == "approve") {
                print ' class="is-active"';
            }
            print '>Approve</a></li>';

            print '<li>';
            print '<a href="' . $adminPath . 'remove.php"';
            if ($path_parts['filename'] == "remove") {
                print ' class="is-active"';
            }
            print '>Remove</a></li>';
            
            print '<li>';
            print '<a href="' . $adminPath . 'images.php"';
            if ($path_parts['filename'] == "images") {
                print ' class="is-active"';
            }
            print '>Manage Images</a></li>';
        }
        ?>
    </ul>
</nav>
<!-- #################### Ends Main Navigation    ########################## -->

