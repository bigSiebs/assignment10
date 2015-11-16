<!-- ######################     Main Navigation   ########################## -->
<nav>
    <ol>
        <?php
        // This sets the current page to not be a link. Repeat this if block for
        //  each menu item 
        if ($path_parts['filename'] == "index") {
            print '<li class="activePage">Home</li>';
        } else {
            print '<li><a href="index.php">Home</a></li>';
        }
        
        if ($path_parts['filename'] == "top-10") {
            print '<li class="activePage">The Top 10</li>';
        } else {
            print '<li><a href="top-10.php">The Top 10</a></li>';
        }
        
        if ($path_parts['filename'] == "not-10") {
            print '<li class="activePage">The Others</li>';
        } else {
            print '<li><a href="tables.php">The Others</a></li>';
        }
        
        if ($path_parts['filename'] == "form") {
            print '<li class="activePage">Suggest an Activity!</li>';
        } else {
            print '<li><a href="form.php">Suggest an Activity!</a></li>';
        }
        
        if ($path_parts['filename'] == "about") {
            print '<li class="activePage">About the List</li>';
        } else {
            print '<li><a href="about.php">About the List</a></li>';
        }
        
        if (adminCheck($username)) {
            if ($path_parts['filename'] == "admin") {
                print '<li class="activePage">Admin</li>';
            } else {
                print '<li><a href="admin.php">Admin</a></li>';
            }
        }
        
        ?>
    </ol>
</nav>
<!-- #################### Ends Main Navigation    ########################## -->

