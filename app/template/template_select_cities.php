<?php foreach ($cities as $key => $val) { ?>
    <option value="<?= $val['id'] ?>" <?= ($myCity == $val['id']) ? 'selected' : '' ?>><?= $val['name'] ?></option>
<?php } ?>