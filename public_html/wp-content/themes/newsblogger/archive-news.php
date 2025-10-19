<?php get_header(); ?>

<main id="primary" class="site-main container">

    <header class="page-header">
        <h1 class="page-title">Tin tức Game</h1>
    </header>

    <div class="news-archive-grid">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('news-archive-item'); ?>>
                    <a href="<?php the_permalink(); ?>">
                        <?php if ( has_post_thumbnail() ) {
                            the_post_thumbnail('medium');
                        } ?>
                        <h2 class="news-title"><?php the_title(); ?></h2>
                    </a>
                    <span class="news-date"><?php echo get_the_date(); ?></span>
                </article>
            <?php endwhile; ?>

            <div class="pagination">
                <?php the_posts_pagination(); ?>
            </div>
        <?php else : ?>
            <p>Chưa có bài viết Tin tức nào.</p>
        <?php endif; ?>
    </div>

</main>

<?php get_footer(); ?>
