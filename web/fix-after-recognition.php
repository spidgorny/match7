<?php

# Sometimes (often?) 6 and 8 are hard to recognize and they are mixed up.
# docs/daydata.json is one such example

$data = json_decode(file_get_contents(__DIR__ . '/../docs/daydata.json'));
print_r($data);
