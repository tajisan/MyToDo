<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Todo;
use App\Library\BaseClass;
use Illuminate\Support\Facades\Auth;

class TodoController extends Controller{
    public function __construct(){
        // ログインしていないとアクションにアクセス出来ないようにする
        $this->middleware('auth');
        // セッションの初期値を設定
        session(['refine' => '/']);
        session(['sort' => 'created_at']);
        session(['order' => 'desc']);
    }

    public function index(Request $request){
        // フォルダ一覧を取得
        $folders = BaseClass::getfolders();
        // completedセッションに値を設定
        session(['completed' => false]);
        // redirectセッションに値を設定
        session(['redirect' => '/']);
        // ログインユーザーの未達成のToDo一覧を取得
        if(session('refine') == '/'):
            $todos = Todo::where([
                ['user_id', Auth::id()], ['complete', false],
            ])
            // あいまい検索
            ->where(function($todos) use($request){
                $todos->where('title', 'like', '%'. $request->search.'%')
                    ->orwhere('explanation', 'like', '%'. $request->search. '%');
            })
            // 並べ替え
            ->orderBy(session('sort'), session('order'))
            ->orderBy('deadline_time', session('order'))
            ->paginate(5);
        // ログインユーザーの未達成で期限間近のToDo一覧を取得
        elseif(session('refine') == '/duesoon'):
            $todos = Todo::where(function($todos) use($request){
                $todos->where('user_id', Auth::id())
                    ->where('complete', false)
                    ->where('deadline', '<' , date("Y-m-d", strtotime('+4 day'))
                );
            })
            // あいまい検索
            ->where(function($todos) use($request){
                $todos->where('title', 'like', '%'. $request->search. '%')
                    ->orwhere('explanation', 'like', '%'. $request->search. '%');
            })
            // 期限の判定
            ->where(function($todos){
                $todos->where('deadline', '>' , date("Y-m-d"))
                ->orwhere(function($todos){
                    $todos->where('deadline', '=' , date("Y-m-d"))
                        ->where('deadline_time', '>', date("H:i:s"));
                })->orwhere(function($todos){
                    $todos->where('deadline', '=', date("Y-m-d"))
                        ->whereNull('deadline_time');
                });
            })
            // 並べ替え
            ->orderBy(session('sort'), session('order'))
            ->orderBy('deadline_time', session('order'))
            ->paginate(5);
        // ログインユーザーの未達成で期限超過のToDo一覧を取得
        elseif(session('refine') == '/overdue'):
            $todos = Todo::where(function($todos){
                $todos->where('user_id', Auth::id())
                    ->where('complete', false);
            })
            // あいまい検索
            ->where(function($todos) use($request){
                $todos->where('title', 'like', '%'. $request->search. '%')
                    ->orwhere('explanation', 'like', '%'. $request->search. '%');
            })
            // 期限の判定
            ->where(function($todos){
                $todos->where('deadline', '<' , date("Y-m-d"))
                ->orwhere(function($todos){
                    $todos->where('deadline', '=' , date("Y-m-d"))
                        ->where('deadline_time', '<', date("H:i:s"));
                });
            })
            // 並べ替え
            ->orderBy(session('sort'), session('order'))
            ->orderBy('deadline_time', session('order'))
            ->paginate(5);
        endif;
        // $todosを渡してindexビューを返す
        return view('todo.index', ['todos' => $todos, 'search' => $request->search, 'folders' => $folders]);
    }


    public function index_completed(Request $request){
        // フォルダ一覧を取得
        $folders = BaseClass::getfolders();
        // completedセッションに値を設定
        session(['completed' => true]);
        // redirectセッションに値を設定
        session(['redirect' => '/index_completed']);
        // refineに'/duesoon'が入っている場合は'/'に変更
        if(session('refine') == '/duesoon'){
            session(['refine' => '/']);
        }
        // ログインユーザーの達成済みのToDo一覧を取得
        if(session('refine') == '/'):
            $todos = Todo::where([
                ['user_id', Auth::id()], ['complete', true]
            ])
            // あいまい検索
            ->where(function($todos) use($request){
                $todos->where('title', 'like', '%'. $request->search. '%')
                    ->orwhere('explanation', 'like', '%'. $request->search. '%');
            })
            // 並べ替え
            ->orderBy(session('sort'), session('order'))
            ->orderBy('deadline_time', session('order'))
            ->paginate(5);
        // ログインユーザーの達成済みで期限超過のToDo一覧を取得
        elseif(session('refine') == '/overdue'):
            $todos = Todo::where(function($todos){
                $todos->where('user_id', Auth::id())
                    ->where('complete', true);
            })
            // あいまい検索
            ->where(function($todos) use($request){
                $todos->where('title', 'like', '%'. $request->search. '%')
                    ->orwhere('explanation', 'like', '%'. $request->search. '%');
            })
            // 期限の判定
            ->where(function($todos){
                $todos->whereColumn('deadline', '<' , 'completed_date')
                ->orwhere(function($todos){
                    $todos->whereColumn('deadline', '=' , 'completed_date')
                        ->whereColumn('deadline_time', '<', 'completed_time');
                });
            })
            // 並べ替え
            ->orderBy(session('sort'), session('order'))
            ->orderBy('deadline_time', session('order'))
            ->paginate(5);
        endif;
        // $todosを渡してindex_completedビューを返す
        return view('todo.index_completed', ['todos' => $todos, 'folders' => $folders]);
    }

    // 絞り込み条件をリセットする
    public function index_all(){
        session(['refine' => '/']);
        // 達成済みか判定してリダイレクト
        if(session('completed')):
            return redirect('/index_completed');
        else:
            return redirect('/');
        endif;
    }

    // 絞り込み条件に期限間近をセットする
    public function duesoon(){
        // redirectセッションに値を設定
        session(['refine'=> "/duesoon"]);
        return redirect('/');
    }

    // 絞り込み条件に期限超過をセットする
    public function overdue(){
        // redirectセッションに値を設定
        session(['refine'=> "/overdue"]);
        // 達成済みか判定してリダイレクト
        if(session('completed')):
            return redirect('/index_completed');
        else:
            return redirect('/');
        endif;
    }

    // 並べ替え条件に作成日時をセットする
    public function index_created_at(){
        // sortに既にcreated_atが設定されている場合は並び順を反転
        if(session('sort') == 'created_at'):
            if(session('order') == 'desc'):
                session(['order' => 'asc']);
            elseif(session('order') == 'asc'):
                session(['order' => 'desc']);
            endif;
        // sortにcreated_atが設定されていなかったら設定 & 並び順の初期化
        else:
            // sortにcreated_atを設定
            session(['sort' => 'created_at']);
            // 並び順をdescに設定
            session(['order' => 'desc']);
        endif;
        // 達成済みか判定してリダイレクト
        if(session('completed')):
            return redirect('/index_completed');
        else:
            return redirect('/');
        endif;
    }

    // 並べ替え条件に目標期限をセットする
    public function index_deadline(){
        // sortに既にdeadlineが設定されている場合は並び順を反転
        if(session('sort') == 'deadline'):
            if(session('order') == 'desc'):
                session(['order' => 'asc']);
            elseif(session('order') == 'asc'):
                session(['order' => 'desc']);
            endif;
        // sortにdeadlineが設定されていなかったら設定 & 並び順の初期化
        else:
            // sortにdeadlineを設定
            session(['sort' => 'deadline']);
            // 並び順をascに設定
            session(['order' => 'asc']);
        endif;
        // 達成済みか判定してリダイレクト
        if(session('completed')):
            return redirect('/index_completed');
        else:
            return redirect('/');
        endif;
    }

    // 並べ替え条件に難易度をセットする
    public function index_difficulty(){
        // sortに既にdifficiltyが設定されている場合は並び順を反転
        if(session('sort') == 'difficulty'):
            if(session('order') == 'desc'):
                session(['order' => 'asc']);
            elseif(session('order') == 'asc'):
                session(['order' => 'desc']);
            endif;
        // sortにdifficultyが設定されていなかったら設定 & 並び順の初期化
        else:
            // sortにdifficultyを設定
            session(['sort' => 'difficulty']);
            // 並び順をascに設定
            session(['order' => 'asc']);
        endif;
        // 達成済みか判定してリダイレクト
        if(session('completed')):
            return redirect('/index_completed');
        else:
            return redirect('/');
        endif;
    }

    // 並べ替え条件に重要度をセットする
    public function index_importance(){
        // sortに既にimportanceが設定されている場合は並び順を反転
        if(session('sort') == 'importance'):
            if(session('order') == 'desc'):
                session(['order' => 'asc']);
            elseif(session('order') == 'asc'):
                session(['order' => 'desc']);
            endif;
        // sortにimportanceが設定されていなかったら設定 & 並び順の初期化
        else:
            // sortにimportanceを設定
            session(['sort' => 'importance']);
            // 並び順をascに設定
            session(['order' => 'asc']);
        endif;
        // 達成済みか判定してリダイレクト
        if(session('completed')):
            return redirect('/index_completed');
        else:
            return redirect('/');
        endif;
    }

    public function create(){
        // フォルダ一覧を取得
        $folders = BaseClass::getfolders();
        // createビューを返す
        return view('todo.create', ['folders' => $folders]);
    }


    public function store(Request $request){
        // バリデーションを設定する
        $request->validate([
            'title'=>'required|string|max:40',
            'explanation'=>'nullable|string|max:500',
            'difficulty'=>'required|integer|max:3',
            'importance'=>'required|integer|max:3',
            'deadline'=>'required|string|max:10',
            'deadline_time'=>'nullable|string',
        ]);
        // $todoに値を設定する
        $todo = new Todo;
        $todo->title = $request->title;
        $todo->explanation = $request->explanation;
        $todo->difficulty = $request->difficulty;
        $todo->importance = $request->importance;
        $todo->complete = false;
        $todo->deadline = $request->deadline;
        $todo->user_id = Auth::id();
        // deadline_timeが送られてきた場合は設定する
        if($request->deadline_time){
            $todo->deadline_time = $request->deadline_time;
        }
        // データベースに保存
        $todo->save();
        // flash_messageセッションにメッセージを代入
        session()->flash('flash_message', 'ToDoの追加が完了しました');
        // リダイレクトする
        return redirect('/');
    }


    public function edit(Request $request, $id){
        // フォルダ一覧を取得
        $folders = BaseClass::getfolders();
        // $idをもつToDoを抜き出す
        $todo = Todo::find($id);
        // $todoを渡してeditビューを返す
        return view('todo.edit', ['todo' => $todo, 'folders' => $folders]);
    }


    public function update(Request $request){
        // バリデーションを設定する
        $request->validate([
            'title'=>'required|string|max:40',
            'explanation'=>'nullable|string|max:500',
            'difficulty'=>'required|integer|max:3',
            'importance'=>'required|integer|max:3',
            'deadline'=>'required|string|max:10',
            'deadline_time'=>'nullable|string|max:8',
            'completed_date'=>'filled|string|max:10',
            'completed_time'=>'filled|string|max:8',
        ]);
        // $todoに値を設定する
        $todo = Todo::find($request->id);
        $todo->title = $request->title;
        $todo->explanation = $request->explanation;
        $todo->difficulty = $request->difficulty;
        $todo->importance = $request->importance;
        $todo->deadline = $request->deadline;
        // deadline_timeが送られてきた場合は設定する
        if($request->deadline_time){
            $todo->deadline_time = $request->deadline_time;
        }
        // completed_dateが送られてきた場合は設定する
        if($request->completed_date){
            $todo->completed_date = $request->completed_date;
        }
        // completed_timeが送られてきた場合は設定する
        if($request->completed_time){
            $todo->completed_time = $request->completed_time;
        }
        // データベースに保存
        $todo->save();
        // flash_messageセッションにメッセージを代入
        session()->flash('flash_message', 'ToDoの編集が完了しました');
        // session('completed')がtrueだったらindex_completedにリダイレクトする
        if(session('completed')){
            return redirect('/index_completed');
        }else{
            return redirect('/');
        }
    }


    public function delete_confirm(Request $request, $id){
        // フォルダ一覧を取得
        $folders = BaseClass::getfolders();
        // $idをもつToDoを抜き出す
        $todo = Todo::find($id);
        // $todoを渡してdelete_confirmビューを返す
        return view('todo.delete_confirm', ['todo' => $todo, 'folders' => $folders]);
    }


    public function delete(Request $request){
        // 受け取ったidのToDoを削除する
        Todo::where('id', $request->id)->delete();
        // flash_messageセッションにメッセージを代入
        session()->flash('flash_message', '削除が完了しました');
        // session('completed')がtrueだったらindex_completedにリダイレクトする
        if(session('completed')){
            return redirect('/index_completed');
        }else{
            return redirect('/');
        }
    }


    public function complete_confirm(Request $request, $id){
        // フォルダ一覧を取得
        $folders = BaseClass::getfolders();
        // $idをもつToDoを抜き出す
        $todo = Todo::find($id);
        // $todoを渡してcomplete_confirmビューを返す
        return view('todo.complete_confirm', ['todo' => $todo, 'folders' => $folders]);
    }


    public function complete(Request $request){
        // $idをもつToDoを抜き出す
        $todo = Todo::find($request->id);
        // 値を設定する
        $todo->complete = true;
        $todo->completed_date = date("Y-m-d");
        $todo->completed_time = date("H:i:s");
        // データベースに保存する
        $todo->save();
        // flash_messageセッションにメッセージを代入
        session()->flash('flash_message', 'ToDoを達成しました');
        // リダイレクトする
        return redirect('/');
    }


    public function release_confirm(Request $request, $id){
        // フォルダ一覧を取得
        $folders = BaseClass::getfolders();
        // $idをもつToDoを抜き出す
        $todo = Todo::find($id);
        // $todoを渡してrelease_confirmビューを返す
        return view('todo.release_confirm', ['todo' => $todo, 'folders' => $folders]);
    }


    public function release(Request $request){
        // $idをもつToDoを抜き出す
        $todo = Todo::find($request->id);
        // 値を設定する
        $todo->complete = false;
        $todo->completed_date = null;
        $todo->completed_time = null;
        // データベースに保存する
        $todo->save();
        // flash_messageセッションにメッセージを代入
        session()->flash('flash_message', 'ToDoの達成状態を解除しました');
        // リダイレクトする
        return redirect('/index_completed');
    }
}
