<?php
$root = dirname(__DIR__);
$private_key = file_get_contents("$root/key");
return array(
  "root" => $root,
  "routes" => "$root/global/routes.php",
  "log_path" => "$root/log",
  "env" => "dev",
  "private_key" => "sb+z1mE5UZQRKZt6ABCiZwe7Fj5ILfVtE0qtxa2X9gB5hGexdvA2PjxO
/LOKVEeJqsLM5HOjQIJOHamWa9Rz7d/uaKGe2yv9X5WPb60BVMkiVJnQ51S31crW
e8NsN3+cqFsAkDCDAgMBAAECggGANvFFQUQsWhYasMYYODzwfKYAm1A+MpyGfU2I
ZxzBYkMLK9iT9e1nBf0I0KtvvQbDxepjXW6E/50wSmqQb2rcCW5u6471C871yFJQ
5tR3B9Lq+9zaIOXsU9h1BzdFPN+oC6l/agznGmK6x3pSr8Z6CpqNCq8H8W9dxZU7
QFx2QzgFryM8Erv0b1X1kDRQrKNOrjFO/xWj6qFuNiKCWFuyKf1Wl4vEJ7uFHiXH
eRrdxeAyhfdEOogrFwSstQ0c0GFoFgkiGpTPH8npORHG06IEIjy3M90Pw0NoMjfe
mSj3MuCnvl0SmGSi7Rc5RWQgJZ",
  "db_info" => [
    "host" => "localhost",
    "user" => "root",
    "password" => "root",
    "database" => "akaunti"
  ],

);
