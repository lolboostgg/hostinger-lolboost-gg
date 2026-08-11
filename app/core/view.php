<?php

# Create new view instance

$templates = new League\Plates\Engine(SYS_PATH . '/public/views');

# You can add global data into the views

#$templates->addData(['name' => 'Jonathan']);

/**
 * @param string $view_file choose file from views folder
 * @param array $data pass data to view file
 * @return void
 */
function view_file(string $view_file, array $data = [])
{
    global $templates;

    echo $templates->render($view_file, $data);
}
/**
 * @param string $view_file choose file from views folder
 * @param array $data pass data to view file
 * @return void
 */
function view_file_store(string $view_file, array $data = [])
{
    global $templates;

    return $templates->render($view_file, $data);
}
/**
 * @param array $data pass global data to all views
 * @return void
 */
function view_add_data(array $data)
{
    global $templates;

    $templates->addData($data);
}
