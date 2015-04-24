<?php

?>

<div id="wuto-task-{$task.id}" class="wuto-task" rel="{$task.id}" data-id="{$task.id}" data-revision="{$task.revision}" aria-label="{$task.label}" draggable="">
    <div class="wuto-task-body">
        <div class="wuto-task-header">

            <?php if(@$this->options->task->show_starred){ ?>

                <a class="wuto-task-star" aria-hidden="true" tabindex="-1">
                    <span class="wundercon star <?php echo ((@$task->starred) ? 'has' : ''); ?>" title="starred"></span>
                </a>

            <?php } ?>

            <?php if(@$this->options->task->show_note){ ?>

                <a class="wuto-task-note" data-type="click" data-action="toggleNote" data-toggle=".wuto-note-content" aria-hidden="true" tabindex="-1">
                    <span class="wundercon note <?php echo ((@$task->has_note) ? 'has' : ''); ?>" title="note"></span>
                </a>

            <?php } ?>

            <div class="wuto-task-title">{$task.title}</div>

        </div>

        <div class="wuto-task-content">

            <?php if((@$this->options->task->show_note || @$this->options->task->note_collapse) && @$task->has_note){ ?>

                <div id="wuto-note-{$task.note.id}" class="wuto-note" rel="{$task.note.id}" data-id="{$task.note.id}" data-revision="{$task.note.revision}" aria-label="note">
                    <div class="wuto-note-content <?php echo ((@$this->options->task->note_collapse) ? '' : 'hide'); ?>">{$task.note.content}</div>
                </div>

            <? } ?>

        </div>
    </div>
</div>