<?php

// System utils.
include "system/utils/assets_url.php";
include "system/utils/loader.php";
include "system/utils/print.php";
include "system/utils/urls.php";

// System core.
include "system/Core.php";

// System masters.
include "system/masters/MasterController.php";
include "system/masters/MasterModel.php";

// System database.
include "system/database/DbConnect.php";
include "system/database/DbInstance.php";

// System managers.
include "system/UserConfigManager.php";
include "system/UserConfigListManager.php";

// System router.
include "system/Router.php";

// Starts the core.
$core = new Core();
$core->start();
