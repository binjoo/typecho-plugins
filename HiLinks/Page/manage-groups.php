<?php
include 'common.php';
include 'header.php';
include 'menu.php';

\TypechoPlugin\HiLinks\Widget\Groups\Admin::alloc()->to($groups);
$linksUrl = Utils\Helper::url("HiLinks/Page/manage-links.php");
$groupsUrl = Utils\Helper::url("HiLinks/Page/manage-groups.php");
?>

<div class="main">
    <div class="body container">
        <div class="typecho-page-title">
            <h2><?php echo $menu->title; ?>
              <a href="<?php _e($linksUrl); ?>"><?php _e("链接") ?></a>
            </h2>
        </div>
        <div class="row typecho-page-main manage-metas">
            <div class="col-mb-12 col-tb-8" role="main">
                <form method="post" name="manage_links" class="operate-form">
                    <div class="typecho-list-operate clearfix">
                        <div class="operate">
                            <label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all"/></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i
                                        class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i
                                        class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a lang="<?php _e('此分组下的所有链接将被删除, 你确认要删除这些分组吗？'); ?>"
                                          href="<?php $security->index('/action/Links?group&do=delete'); ?>"><?php _e('删除'); ?></a>
                                    </li>
                                    <li class="multiline">
                                        <button type="button" class="btn merge btn-s"
                                                rel="<?php $security->index('/action/Links?group&do=merge'); ?>"><?php _e('移动到'); ?></button>
                                        <select name="merge">
                                            <?php $groups->parse('<option value="{mid}">{name}</option>'); ?>
                                        </select>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div><!-- end .typecho-list-operate -->

                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table">
                            <colgroup>
                                <col width="20" class="kit-hidden-mb" />
                                <col width="35%" />
                                <col class="kit-hidden-mb" />
                                <col width="10%" class="kit-hidden-mb"/>
                            </colgroup>
                            <thead>
                            <tr class="nodrag">
                                <th class="kit-hidden-mb"></th>
                                <th><?php _e('名称'); ?></th>
                                <th class="kit-hidden-mb"><?php _e('描述'); ?></th>
                                <th class="kit-hidden-mb"><?php _e('链接数'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($groups->have()): ?>
                                <?php while ($groups->next()): ?>
                                    <tr id="<?php $groups->theId(); ?>">
                                        <td class="kit-hidden-mb">
                                          <input type="checkbox" value="<?php $groups->mid(); ?>" name="mid[]"/>
                                        </td>
                                        <td>
                                          <a href="<?php _e($groupsUrl . '&mid=' . $groups->mid); ?>"><?php $groups->name(); ?></a>
                                          <a title="<?php _e('编辑 %s', htmlspecialchars($groups->name)); ?>"
                                             href="<?php _e($groupsUrl . '&mid=' . $groups->mid); ?>"><i
                                             class="i-edit"></i></a>
                                        </td>
                                        <td>
                                          <?php $groups->description(); ?>
                                        </td>
                                        <td>
                                          <a class="balloon-button left"
                                            href="<?php _e($linksUrl . '&mid=' . $groups->mid); ?>"
                                            ><?php $groups->count(); ?></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">
                                      <h6 class="typecho-list-table-title"><?php _e('没有任何分组'); ?></h6>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </form><!-- end .operate-form -->
            </div>
            <div class="col-mb-12 col-tb-4" role="form">
                <?php \TypechoPlugin\HiLinks\Widget\Groups\Edit::alloc()->form()->render(); ?>
            </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
?>
<style>
.link-icon{
  margin-top: -8px;
  margin-bottom: -11px;
  width: 32px;
  height: 32px;
}
</style>
<script type="text/javascript">
    (function () {
        $(document).ready(function () {
            var table = $('.typecho-list-table').tableDnD({
                onDrop: function () {
                    var ids = [];

                    $('input[type=checkbox]', table).each(function () {
                        ids.push($(this).val());
                    });

                    $.post('<?php $security->index('/action/Links?group&do=sort'); ?>',
                        $.param({mid: ids})
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

