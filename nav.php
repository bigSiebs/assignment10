<!-- ######################     Main Navigation   ########################## -->
<nav class="top-bar-left">
    <ol class="menu text-center">
        <?php
        // This sets the current page to not be a link. Repeat this if block for
        //  each menu item

        if ($path_parts['filename'] == "index") {
            print '<li class="menu-text">Home</li>';
        } else {
            print '<li><a href="' . $path . 'index.php">Home</a></li>';
        }

        if ($path_parts['filename'] == "list") {
            print '<li class="menu-text">The List</li>';
        } else {
            print '<li><a href="' . $path . 'list.php">The List</a></li>';
        }

        if ($path_parts['filename'] == "form") {
            print '<li class="menu-text">Add an Activity</li>';
        } else {
            print '<li><a href="' . $path . 'form.php">Add an Activity</a></li>';
        }

        if ($path_parts['filename'] == "about") {
            print '<li class="menu-text">About the List</li>';
        } else {
            print '<li><a href="' . $path . 'about.php">About the List</a></li>';
        }

        if (adminCheck($thisDatabaseReader, $username)) {
            if ($path_parts['filename'] == "approve") {
                print '<li class="menu-text">Approve</li>';
            } else {
                print '<li><a href="' . $adminPath . 'approve.php">Approve</a></li>';
            }
            
            if ($path_parts['filename'] == "remove") {
                print '<li class="menu-text">Remove</li>';
            } else {
                print '<li><a href="' . $adminPath . 'remove.php">Remove</a></li>';
            }
        }
        ?>
    </ol>
</nav>
<!-- #################### Ends Main Navigation    ########################## -->

