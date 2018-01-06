<?php
/**
 * @package     Solo
 * @copyright   2014-2017 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 3 or later
 */

use Awf\Text\Text;

// Used for type hinting
/** @var \Solo\View\Main\Html $this */

$adblock = <<<JS
Solo.System.documentReady(function(){
        var test = document.createElement('div');
        test.innerHTML = '&nbsp;';
        test.className = 'adsbox';
        document.body.appendChild(test);
        window.setTimeout(function() {
            if (test.offsetHeight === 0) {
                document.getElementById('adblock-warning').style.display = 'block';
            }
            test.remove();
        }, 100);
    });
JS;

$this->getContainer()->application->getDocument()->addScriptDeclaration($adblock);

?>
<div id="adblock-warning" class="alert-danger alert" style="display: none;">
	<?php echo Text::_('SOLO_SETUP_LBL_ADBLOCK_WARNING')?>
</div>
