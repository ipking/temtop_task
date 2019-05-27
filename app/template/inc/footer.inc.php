<?php

use Lite\Core\Application;
use function Lite\func\microtime_diff;

?>
</section>
<footer class="footer">
	<p>深圳市腾拓科技有限公司@<?=date('Y');?>版权所有
	</p>
	&copy;<?=date('Y');?> Temtop All Rights Reserved, ST: <?=number_format(microtime_diff(Application::$init_microtime)*1000, 1, null, '');?>ms
</footer>
</section>
</body>
</html>