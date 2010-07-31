<?php

$link = "{$vars['url']}install.php?step={$vars['next_step']}";

echo <<<___END
<div class="install_nav">
	<a href="$link">Next</a>
</div>

___END;
