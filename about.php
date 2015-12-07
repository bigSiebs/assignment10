<?php
include "top.php";
?>

<article id="Project">
    <h2>About this Project</h2>
    <div class="row">
        <p>With so much to do and limited time to do it all, we wanted to help UVM students find fun and exciting activities amid their busy schedule.</p>
        <p>Our solution is to provide a reddit-like site detailing activities for college students in the Burlington area.</p>
        <p>If you notice anything about the site that we should know about (e.g., inappropriate content, a feature that's not working), please let us know! We can be reached at <a href="mailto:jsiebert@uvm.edu">jsiebert@uvm.edu</a> or <a href="mailto:aychu@uvm.edu">aychu@uvm.edu</a>.</p>
    </div>
    <section id="about-us">
        <h3>The Coders</h3>
        <div class="row panel">
            <div class="large-4 columns">
                <figure>
                    <img alt="Joe Siebert" src="<?php print $path ?>images/joe.png">
                    <figcaption>
                        <p>Joe Siebert</p>
                    </figcaption>
                </figure>
            </div>
            <div class="large-8 columns">
                <p> Joe, the project manager, likes getting it done. He is patient and hardworking, making a great leader as well as a good friend.</p>
            </div>
        </div>
        <div class="row panel">
            <div class="large-4 columns">
                <figure>
                    <img alt="Alan Chu" src="<?php print $path ?>images/alan.png">
                    <figcaption>
                        <p>Alan Chew</p>
                    </figcaption>
                </figure>
            </div>
            <div class="large-8 columns">
                <p>Every Batman has a Robin. Alan can sometimes be a wildcard, but that makes the journey ever more interesting. When his passion aligns with his vision, he can be a formidable sidekick and coding buddy.</p>
            </div>
        </div>
    </section>
</article>

<?php
include "footer.php";
?>