<select class="changeUserLists">
    <?php foreach ($userLists as $key => $val) { ?>
        <option value="<?= $val['id'] ?>" <?= ($list_id == $val['id']) ? 'selected' : '' ?>>
            <?= $val['name'] ?>
        </option>
    <?php } ?>
</select>