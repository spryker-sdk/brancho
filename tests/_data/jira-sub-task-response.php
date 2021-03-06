<?php

return [
  'expand' => 'renderedFields,names,schema,operations,editmeta,changelog,versionedRepresentations',
  'id' => '9453464356345134',
  'self' => 'https://jira-url/rest/api/2/issue/9123',
  'key' => 'RK-456',
  'fields' =>
  [
    'parent' => [
        'key' => 'RK-123' // the parent issue
    ],
    'issuetype' =>
    [
      'self' => 'https://jira-url/rest/api/2/issuetype/1',
      'id' => '1',
      'description' => 'A problem which impairs or prevents the functions of the product.',
      'iconUrl' => 'https://jira-url/secure/viewavatar?size=medium&avatarId=10303&avatarType=issuetype',
      'name' => 'Sub-Task',
      'subtask' => false,
      'avatarId' => 10303,
    ],
    'summary' => 'Sub-Task summary',
  ],
];
