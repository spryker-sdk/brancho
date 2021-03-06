<?php

/**
 * Copyright © 2019-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Brancho\Jira;

use chobie\Jira\Api;
use chobie\Jira\Api\Authentication\Basic;
use chobie\Jira\Api\Result;

/**
 * @codeCoverageIgnore Jira uses only mocks for testing.
 */
class Jira
{
    /**
     * @param string $issue
     * @param array $config
     *
     * @return array
     */
    public function getJiraIssue(string $issue, array $config): array
    {
        $api = new Api($config['host'], new Basic($config['username'], $config['password']));

        $result = $api->api(Api::REQUEST_GET, sprintf('/rest/api/2/issue/%s', $issue));

        if ($result instanceof Result) {
            return $result->getResult();
        }

        return [];
    }
}
