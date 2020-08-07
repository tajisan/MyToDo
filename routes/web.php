<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'TodoController@index');
Route::get('index_completed', 'TodoController@index_completed');
// 絞り込み
Route::prefix('index/refine')->group(function(){
  Route::get('all', 'TodoController@index_all');
  Route::get('duesoon', 'TodoController@duesoon');
  Route::get('overdue', 'TodoController@overdue');
});
// 並べ替え
Route::prefix('index/sort')->group(function(){
  Route::get('created_at', 'TodoController@index_created_at');
  Route::get('deadline', 'TodoController@index_deadline');
  Route::get('difficulty', 'TodoController@index_difficulty');
  Route::get('importance', 'TodoController@index_importance');
  Route::get('completed_date', 'TodoController@index_completed_date');
});

Route::prefix('todo')->group(function(){
  Route::get('create', 'TodoController@create');
  Route::post('create', 'TodoController@store');
  Route::get('edit/{id}', 'TodoController@edit');
  Route::post('edit', 'TodoController@update');
  Route::get('delete_confirm/{id}', 'TodoController@delete_confirm');
  Route::post('delete', 'TodoController@delete');
  Route::get('complete_confirm/{id}', 'TodoController@complete_confirm');
  Route::post('complete', 'TodoController@complete');
  Route::get('release_confirm/{id}', 'TodoController@release_confirm');
  Route::post('release', 'TodoController@release');
});

Route::get('profile', 'UserController@profile');
Route::get('register', 'UserController@register_form');
Route::post('register', 'UserController@register');
Route::get('login', 'UserController@login_form');
Route::post('login', 'UserController@login');
Route::get('logout', 'UserController@logout');
Route::get('user_delete_confirm/{id}', 'UserController@user_delete_confirm');
Route::post('user_delete', 'UserController@user_delete');

Route::get('folder_create_form', 'FolderController@folder_create_form');
Route::post('folder_create', 'FolderController@folder_create');
Route::get('folder_index/{id}', 'FolderController@folder_index');
Route::get('folder_index_completed/{id}', 'FolderController@folder_index_completed');
Route::get('add_folder_form/{id}', 'FolderController@add_folder_form');
Route::get('add_folder_completed_form/{id}', 'FolderController@add_folder_completed_form');
Route::get('add_folder/{folder_id}/{todo_id}', 'FolderController@add_folder');
Route::get('delete_folder_confirm/{folder_id}', 'FolderController@delete_folder_confirm');
Route::post('delete_folder', 'FolderController@delete_folder');
Route::get('folder_release_confirm/{folder_id}/{todo_id}', 'FolderController@folder_release_confirm');
Route::post('folder_release', 'FolderController@folder_release');

// フォルダ画面絞り込み
Route::get('folder_index_all/{folder_id}', 'FolderController@folder_index_all');
Route::get('folder_index_duesoon/{folder_id}', 'FolderController@folder_index_duesoon');
Route::get('folder_index_overdue/{folder_id}', 'FolderController@folder_index_overdue');
// フォルダ画面並べ替え
Route::get('folder_index_created_at/{folder_id}', 'FolderController@folder_index_created_at');
Route::get('folder_index_deadline/{folder_id}', 'FolderController@folder_index_deadline');
Route::get('folder_index_difficulty/{folder_id}', 'FolderController@folder_index_difficulty');
Route::get('folder_index_importance/{folder_id}', 'FolderController@folder_index_importance');
Route::get('folder_index_completed_date/{folder_id}', 'FolderController@folder_index_completed_date');

// ToDoフォルダ追加画面絞り込み
Route::get('add_folder_all/{folder_id}', 'FolderController@add_folder_all');
Route::get('add_folder_duesoon/{folder_id}', 'FolderController@add_folder_duesoon');
Route::get('add_folder_overdue/{folder_id}', 'FolderController@add_folder_overdue');
// ToDoフォルダ追加画面並べ替え
Route::get('add_folder_created_at/{folder_id}', 'FolderController@add_folder_created_at');
Route::get('add_folder_deadline/{folder_id}', 'FolderController@add_folder_deadline');
Route::get('add_folder_difficulty/{folder_id}', 'FolderController@add_folder_difficulty');
Route::get('add_folder_importance/{folder_id}', 'FolderController@add_folder_importance');