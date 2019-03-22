<h1>Cloudways service manager</h1>

<h2>Installation:</h2>
<strong>Composer:</strong> <br/>
composer require andyworkbase/magecloud-cloudways-manager <br/>
composer update <br/>

<strong>Manually:</strong> <br/>
1) unpack extension package and upload them into Magento root directory/app/code/
2) php bin/magento setup:upgrade
3) php bin/magento setup:di:compile
4) php bin/magento setup:static-content:deploy

<strong>Manager</strong> - System -> Cache Management -> Cloudways Manager

<strong>Configuration</strong> - Stores -> Configuration -> MageCloud -> Cloudways Manager

<h2>Features:</h2>
<ul>
<li>check Cloudways services state (apache, elasticsearch, mysql, varnish, etc...);</li>
<li>enable Varnish service;</li>
<li>disable Varnish service;</li>
<li>purge Varnish service cache;</li>
<li>automatically purge Varnish service cache (purge cache when clicking the 'Flush Cache Storage' button in System -> Cache Management);</li>
</ul>

<h2>Available CLI commands:</h2>
<ul>
<li>magecloud:cloudways-manager:service-state;</li>
<li>magecloud:cloudways-manager:varnish-enable;</li>
<li>magecloud:cloudways-manager:varnish-disable;</li>
<li>magecloud:cloudways-manager:varnish-purge;</li>
</ul>
