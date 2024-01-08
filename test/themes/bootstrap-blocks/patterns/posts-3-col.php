<?php
/**
 * Title: List of posts, 3 columns
 * Slug: bootstrap-hooks/posts-3-col
 * Categories: query
 * Block Types: core/query
 */
?>

<!-- wp:query {"query":{"perPage":10,"pages":0,"offset":"0","postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true},"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-query alignwide">
	<!-- wp:query-no-results -->
	<!-- wp:pattern {"slug":"bootstrap-hooks/hidden-no-results"} /-->
	<!-- /wp:query-no-results -->

	<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"}}},"layout":{"type":"default"}} -->
	<div class="wp-block-group" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--20);padding-right:0;padding-bottom:var(--wp--preset--spacing--20);padding-left:0">
    
    <!-- wp:post-template {"align":"full","style":{"spacing":{"blockGap":"var:preset|spacing|10"}},"layout":{"type":"grid","columnCount":3}} -->
    
    <!-- wp:group {"className":"card","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"flex","orientation":"vertical","flexWrap":"nowrap"}} -->
    <div class="wp-block-group card h-100">
      <!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","className":"card-img-top"} /-->

      <!-- wp:group {"className":"card-body","style":{"spacing":{"blockGap":"var:preset|spacing|10"}},"layout":{"type":"flex","orientation":"vertical","flexWrap":"nowrap"}} -->
      <div class="wp-block-group card-body">
        <!-- wp:post-title {"level":"5","className":"card-title","isLink":true,"style":{"layout":{"flexSize":"min(2.5rem, 3vw)","selfStretch":"fixed"}}} /-->

        <!-- wp:pattern {"slug":"bootstrap-hooks/hidden-post-meta"} /-->

        <!-- wp:post-excerpt {"style":{"layout":{"flexSize":"min(2.5rem, 3vw)","selfStretch":"fixed"}},"textColor":"contrast-2","fontSize":"small"} /-->
      </div>
          <!-- /wp:group -->
    </div>
    <!-- /wp:group -->

		<!-- /wp:post-template -->

		<!-- wp:spacer {"height":"var:preset|spacing|40","style":{"spacing":{"margin":{"top":"0","bottom":"0"}}}} -->
		<div style="margin-top:0;margin-bottom:0;height:var(--wp--preset--spacing--40)" aria-hidden="true" class="wp-block-spacer"></div>
		<!-- /wp:spacer -->

		<!-- wp:query-pagination {"paginationArrow":"arrow","layout":{"type":"flex","justifyContent":"space-between"}} -->
		<!-- wp:query-pagination-previous /-->
		<!-- wp:query-pagination-next /-->
		<!-- /wp:query-pagination -->

	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:query -->