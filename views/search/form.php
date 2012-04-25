<form method="get" action="<?php e($router->getUrl('SearchResult'))?>"
      class="extended searchform">
  <p class="stringsearch"><label for="s_na">Name:</label>
     <input type="text" name="na" id="s_na" value="" /></p>
  <p class="stringsearch"><label for="s_int">Decimal:</label>
     <input type="number" name="int" id="s_int" value="<?php echo _get('int')?>" /></p>
<?php foreach (array('blk', 'gc', 'bc', 'ccc', 'dt', 'nt', 'lb', 'ea',
                     'sc', 'SB', 'WB') as $cat) {
     include 'fieldset.php';
    } ?>
  <?php foreach ($info->getBooleanCategories() as $cat):?>
  <p class="boolsearch">
    <select name="<?php e($cat)?>" id="s_<?php e($cat)?>" size="3">
      <option value="" <?php if (_get($cat) === ""):?>selected="selected"<?php endif?>>any</option>
      <option value="1" <?php if (_get($cat) === "1"):?>selected="selected"<?php endif?>>yes</option>
      <option value="0" <?php if (_get($cat) === "0"):?>selected="selected"<?php endif?>>no</option>
    </select>
    <label for="s_<?php e($cat)?>"><?php e($info->getCategory($cat))?></label>
  </p>
  <?php endforeach?>
  <p class="submitset"><button type="submit"><span>search</span></button></p>
</form>
