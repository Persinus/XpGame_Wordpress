<?php get_header(); ?>

<main id="primary" class="site-main single-news">

    <?php
    while ( have_posts() ) :
        the_post();
    ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class("news-article"); ?>  style="padding-left:50px;>
            
            <header class="news-header">
                <h1 class="news-title"><?php the_title(); ?></h1>
                
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="news-thumbnail">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>
            </header>

            <div class="news-meta">
                <?php if( get_field('news_source') ): ?>
                    <p><strong>Nguồn:</strong> <?php echo esc_html( get_field('news_source') ); ?></p>
                <?php endif; ?>

                <?php if( get_field('release_date') ): ?>
                    <p><strong>Ngày phát hành:</strong> 
                        <?php echo date_i18n('d/m/Y', strtotime(get_field('release_date'))); ?>
                    </p>
                <?php endif; ?>

                <?php if( get_field('news_type') ): ?>
                    <p><strong>Loại tin tức:</strong> <?php echo esc_html( get_field('news_type') ); ?></p>
                <?php endif; ?>

                <?php if( get_field('highlight') ): ?>
                    <div class="news-highlight">
                        <h3>Điểm nổi bật</h3>
                        <p><?php echo esc_html( get_field('highlight') ); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="news-content">
                <?php the_content(); ?>
            </div>

        </article>
    <?php endwhile; ?>

</main>

<?php get_footer(); ?>
