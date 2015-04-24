<div class="wuto">
    <div id="wuto-{$list.id}" class="wuto-list readonly" data-type="list" data-id="{$list.id}" data-revision="{$list.revision}" data-position-id="{$list.position_id}" data-position-revision="{$list.position_revision}" aria-label="{$list.label}">

        <?php if(@$this->options->list->show_title){ ?>

            <div id="wuto-list-title">
                {$list.title}
            </div>

        <?php } ?>

        <ol class="wuto-tasks sortable">

            <?php foreach($this->tasks as $task) { ?>

                <li>
                    <?php $this->inc(dirname(__FILE__) . '/task.php', array('task' => $task)); ?>
                </li>

            <?php } ?>

        </ol>
    </div>
</div>