<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$wooahanBadge = new wooahanBadge();
$badge_use 	  = $wooahanBadge->is_badge_use($post->ID);
?>

<div id="set-badge" class="section-wrap container-fluid" data-badges='<?php echo json_encode($wooahanBadge->allBadges());?>' data-added-badges='<?php echo json_encode($wooahanBadge->addedBadges($post->ID));?>' data-added-badges-keys='<?php echo json_encode($wooahanBadge->getBadgesKeys($post->ID));?>'>
	<?php
		include (WOOAHAN_PATH . 'includes/admin/edit-permalink.php');
	?>
	<h2>뱃지관리</h2>
	<div class="row">
		<div class="col col-lg-2">뱃지사용</div>
		<div class="col-md-auto"></div>
		<div class="col">
			<div class="form-check form-check-inline">
			  <input class="form-check-input" type="radio" name="wooahan[badge_use]" id="inlineRadio1" value="yes" <?php if($badge_use == true) : echo 'checked'; endif; ?>>
			  <label class="form-check-label" for="inlineRadio1">사용함</label>
			</div>
			<div class="form-check form-check-inline">
			  <input class="form-check-input" type="radio" name="wooahan[badge_use]" id="inlineRadio2" value="no" <?php if($badge_use == false) : echo 'checked'; endif; ?>>
			  <label class="form-check-label" for="inlineRadio2">사용안함</label>
			</div>						
		</div>
	</div>
	<div class="row">
		<div class="col col-lg-2">뱃지 업로드</div>
		<div class="col-md-auto"></div>
		<div class="col">
			타이틀 : <input type="text" id="customBadgeTitle"> 기본 가로사이즈 : <input type="text" id="customBadgeSize">
			<input type="file" id="customBadgeFile" placeholder="등록하실 뱃지를 업로드 하세요"> <button type="button" class="button" v-on:click="badgeUpload">업로드</button>
		</div>
	</div>
	<div class="row">
		<div class="col col-lg-2">뱃지선택</div>
		<div class="col-md-auto"></div>
		<div class="col">
			<div class="badge-list">
				<div class="search-badge">
					<input type="text" v-model="searchKeyword" v-on:keyup="searchBadge" placeholder="뱃지 명칭으로 검색 해 보세요!"><button type="button" class="button">검색</button>
				</div>
				<ul class="badge-ul">
					<template v-for="(badge, key) in badges">
						<li class="badge" v-bind:class="{'custom' : badge.type === 'custom'}" v-bind:data-id="key" v-bind:data-title="badge.title" v-on:click="add(key)"><img v-bind:src="badge.url"><div v-show="badge.type === 'custom'" class="custom-label"><?php _e('사용자 등록', 'wooahan');?><span class="remove" v-on:click.stop v-on:click="customBadgeRemove(badge.url)"><i class="far fa-trash-alt"></i></span></div></li>
					</template>		
				</ul>
			</div>
			<div class="badge-list-under">
				<a href="http://superengine.io" target="_blank"><img src="<?php echo plugins_url('/assets/images/badge-banner.jpg', WOOAHAN__FILE__); ?>"></a>
			</div>
		</div>
	</div>
	<div class="row added-badges-row">
		<div class="col col-lg-2">등록된 뱃지</div>
		<div class="col-md-auto"></div>
		<div class="col">
			<input type="hidden" class="added-badge-keys" name="wooahan[badges_keys]" v-bind:value="addedKeys">
			<ul class="added-badges-ul" v-sortable="{ onUpdate : onUpdate, swapThreshold : 1, animation:150, handle: '.badge-move' }">
				<template v-for="(badge, key) in addedBadges">
					<li class="added-badge">
						<span class="badge-move"><i class="fas fa-ellipsis-v"></i></span>
						<span class="badge-priority">{{ key + 1 }}</span>
						<span class="badge-image">
							<img v-bind:src="badge.url">
						</span>
						<span class="control-wrap">
							<input type="hidden" class="added-badge-url" name="wooahan[badges][][url]" v-bind:value="badge.url">
							<input type="hidden" class="added-badge-title" name="wooahan[badges][][title]" v-bind:value="badge.title">
							<span class="control-item">가로크기 : <input type="text" class="added-badge-width" name="wooahan[badges][][width]" v-bind:value="badge.width"> px</span>
							<span class="control-item">세로크기 : auto</span>
							<span class="control-item">여백설정 : <input type="text" class="added-badge-margin-top" name="wooahan[badges][][margin][top]" placeholder="<?php _e('상단', 'wooahan');?>" v-bind:value="badge.margin.top"><input type="text" class="added-badge-margin-right" name="wooahan[badges[][margin][right]" placeholder="<?php _e('우측', 'wooahan');?>" v-bind:value="badge.margin.right"><input type="text" class="added-badge-margin-bottom" name="wooahan[badges][][margin][bottom]" placeholder="<?php _e('하단', 'wooahan');?>" v-bind:value="badge.margin.bottom"><input type="text" class="added-badge-margin-left" name="wooahan[badges][][margin][left]" placeholder="<?php _e('좌측', 'wooahan');?>" v-bind:value="badge.margin.left"> px</span>
							<span class="control-item remove-item" v-on:click="remove(key)"><i class="fas fa-times"></i></span>
						</span>
					</li>
				</template>
			</ul>
		</div>
	</div>
</div>