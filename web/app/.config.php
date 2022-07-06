<?php
return array (
  'profile' => 
  array (
    'oj-name' => 'Universal Online Judge',
    'oj-name-short' => 'UOJ',
    'administrator' => 'root',
    'admin-email' => 'admin@local_uoj.ac',
    'QQ-group' => '',
    'ICP-license' => '',
  ),
  'database' => 
  array (
    'database' => 'app_uoj233',
    'username' => 'root',
    'password' => 'root',
    'host' => '127.0.0.1',
  ),
  'web' => 
  array (
    'domain' => NULL,
    'main' => 
    array (
      'protocol' => 'http',
      'host' => UOJContext::httpHost(),
      'port' => 80,
    ),
    'blog' => 
    array (
      'protocol' => 'http',
      'host' => UOJContext::httpHost(),
      'port' => 80,
    ),
  ),
  'security' => 
  array (
    'user' => 
    array (
      'client_salt' => 'p7mhmsZLweh0jVsaXeiV9wulgf57CXde',
    ),
    'cookie' => 
    array (
      'checksum_salt' => 
      array (
        0 => 'aIWenOISQXjdlpyu',
        1 => 'jEkOGJCAPumSbU0n',
        2 => 'aTtMYgjB7pTbmkDd',
      ),
    ),
  ),
  'mail' => 
  array (
    'noreply' => 
    array (
      'username' => 'noreply@local_uoj.ac',
      'password' => '_mail_noreply_password_',
      'host' => 'smtp.local_uoj.ac',
      'secure' => 'tls',
      'port' => 587,
    ),
  ),
  'judger' => 
  array (
    'socket' => 
    array (
      'port' => '2333',
      'password' => 'okxdUmTOJ8kYZcQ5L6njdoljAvrpAv19',
    ),
  ),
  'switch' => 
  array (
    'web-analytics' => false,
    'blog-domain-mode' => 3,
  ),
);
