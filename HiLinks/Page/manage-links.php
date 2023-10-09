<?php
include 'common.php';
include 'header.php';
include 'menu.php';

\TypechoPlugin\HiLinks\Widget\Links\Admin::alloc()->to($links);
\TypechoPlugin\HiLinks\Widget\Groups\Admin::alloc()->to($groups);
$linksUrl = Utils\Helper::url("HiLinks/Page/manage-links.php");
$groupsUrl = Utils\Helper::url("HiLinks/Page/manage-groups.php");
?>

<style>
.link-icon{
  margin-top: -8px;
  margin-bottom: -11px;
  width: 32px;
  height: 32px;
}
.status{
  margin-left: 0 !important;
  box-sizing: border-box;
  font-variant: tabular-nums;
  list-style: none;
  font-feature-settings: "tnum";
  display: inline-block;
  height: auto;
  padding: 0 7px;
  line-height: 18px;
  white-space: nowrap;
  border-radius: 2px;
  opacity: 1;
  transition: all .3s;
}
.status.s1{
  background-color: #f0f9eb;
  border-color: #e1f3d8;
  color: #67c23a;
}
.status.s2{
  background-color: #f4f4f5;
  border-color: #e9e9eb;
  color: #909399;
}
.status.s3{
  background-color: #fdf6ec;
  border-color: #faecd8;
  color: #e6a23c;
}
.ellipsis{
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
<div class="main">
    <div class="body container">
        <div class="typecho-page-title">
            <h2><?php echo $menu->title; ?>
              <a href="<?php _e($groupsUrl); ?>"><?php _e("分组") ?></a>
            </h2>
        </div>
        <div class="row typecho-page-main manage-metas">
            <div class="col-mb-12 col-tb-8" role="main">
                <form method="post" name="manage_links" class="operate-form">
                    <div class="typecho-list-operate clearfix">
                        <div class="operate">
                            <label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox"
                                                                                  class="typecho-table-select-all"/></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i
                                        class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i
                                        class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a lang="<?php _e('你确认要删除这些链接吗?'); ?>"
                                          href="<?php $security->index('/action/Links?link&do=delete&mid=' . $request->mid); ?>"><?php _e('删除'); ?></a>
                                    </li>
                                    <li class="multiline">
                                        <button type="button" class="btn merge btn-s"
                                                rel="<?php $security->index('/action/Links?link&do=merge'); ?>"><?php _e('合并到'); ?></button>
                                        <select name="merge">
                                            <?php $groups->parse('<option value="{mid}">{name}</option>'); ?>
                                        </select>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <ul class="typecho-option-tabs right" style="margin: 0">
                            <li class="<?php _e(isset($request->mid) ? '' : 'current') ?>">
                              <a href="<?php _e($linksUrl); ?>"><?php _e('所有'); ?></a>
                            </li>
                            <?php if ($groups->have()): ?>
                                <?php while ($groups->next()): ?>
                            <li class="<?php _e(isset($request->mid) && $groups->mid == $request->mid? 'current' : '') ?>">
                              <a href="<?php _e($linksUrl . '&mid=' . $groups->mid); ?>"><?php $groups->name(); ?></a>
                            </li>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </ul>
                    </div><!-- end .typecho-list-operate -->

                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table">
                            <colgroup>
                                <col width="20" class="kit-hidden-mb" />
                                <col width="50" />
                                <col width="28%" />
                                <col width="10%" class="kit-hidden-mb" />
                                <col class="kit-hidden-mb" />
                            </colgroup>
                            <thead>
                            <tr class="nodrag">
                                <th class="kit-hidden-mb"></th>
                                <th><?php _e('图标'); ?></th>
                                <th><?php _e('名称'); ?></th>
                                <th><?php _e('状态'); ?></th>
                                <th class="kit-hidden-mb"><?php _e('描述'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($links->have()): ?>
                                <?php while ($links->next()): ?>
                                    <tr id="<?php $links->theId(); ?>">
                                        <td class="kit-hidden-mb">
                                          <input type="checkbox" value="<?php $links->lid(); ?>" name="lid[]"/>
                                        </td>
                                        <td>  
                                          <?php if($links->avatar) { ?>
                                            <img class="link-icon" src="<?php $links->avatar(); ?>" />
                                          <?php } else { ?>
                                            <div class="link-icon" style="background-color: #E9E9E6;margin-bottom: -8px;"></div>
                                          <?php } ?>
                                        </td>
                                        <td>
                                          <a href="<?php _e($linksUrl . '&lid=' . $links->lid); ?>"><?php $links->title(); ?></a>
                                          <a title="<?php _e('编辑 %s', htmlspecialchars($links->title)); ?>"
                                            href="<?php _e($linksUrl . '&lid=' . $links->lid); ?>"><i
                                            class="i-edit"></i></a>
                                          <a title="<?php _e('浏览 %s', htmlspecialchars($links->title)); ?>"
                                            href="<?php $links->url();?>"
                                            target="_blank"
                                            rel="noopener noreferrer"><i
                                            class="i-exlink"></i></a>
                                        </td>
                                        <td>
                                          <?php if($links->status == 1) { ?>
                                          <span class="status s1">正常</span>
                                          <?php } elseif ($links->status == 2) { ?>
                                          <span class="status s2">隐藏</span>
                                          <?php } elseif ($links->status == 3) { ?>
                                          <span class="status s3">失联</span>
                                          <?php } ?>
                                        </td>
                                        <td class="ellipsis">
                                          <?php $links->description(); ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">
                                      <h6 class="typecho-list-table-title"><?php _e('没有任何链接'); ?></h6>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </form> 
                <div class="typecho-list-operate clearfix">
                  <?php if ($links->have()): ?>
                      <ul class="typecho-pager">
                          <?php $links->pageNav(); ?>
                      </ul>
                  <?php endif; ?>
                </div>
            </div>
            <div class="col-mb-12 col-tb-4" role="form">
                <?php \TypechoPlugin\HiLinks\Widget\Links\Edit::alloc()->form()->render(); ?>
            </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
?>
<script type="text/javascript">
    (function () {
        $(document).ready(function () {
            var table = $('.typecho-list-table').tableDnD({
                onDrop: function () {
                    var ids = [];

                    $('input[type=checkbox]', table).each(function () {
                        ids.push($(this).val());
                    });

                    $.post('<?php $security->index('/action/Links?link&do=sort&mid=' . $request->mid); ?>',
                        $.param({lid: ids})
                    );
                }
            });

            table.tableSelectable({
                checkEl: 'input[type=checkbox]',
                rowEl: 'tr',
                selectAllEl: '.typecho-table-select-all',
                actionEl: '.dropdown-menu a'
            });

            $('.btn-drop').dropdownMenu({
                btnEl: '.dropdown-toggle',
                menuEl: '.dropdown-menu'
            });

            $('.dropdown-menu button.merge').click(function () {
                var btn = $(this);
                btn.parents('form').attr('action', btn.attr('rel')).submit();
            });
        });
    })();
</script>
<?php include 'footer.php'; ?>

