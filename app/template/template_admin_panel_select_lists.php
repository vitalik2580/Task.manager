<?php foreach ($myLists as $keyList => $valList) { ?>
    <option value="<?= $valList['id'] ?>" <?= ($val['list_id'] == $valList['id']) ? 'selected' : '' ?>>
        <?= $valList['name'] ?>
    </option>
<?php } ?>
