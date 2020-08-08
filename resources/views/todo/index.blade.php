{{-- template.blade.phpの@yield('logo-path')に渡す --}}
@section('logo-path', 'image/mytodo_icon.png')
{{-- templateを読み込む --}}
@extends('layouts.template')

{{-- head.blade.phpの@yield('title')に渡す --}}
@section('title', '未達成')
{{-- head.blade.phpを差し込む --}}
@include('layouts.head')

{{-- ナビゲーションバー --}}
@include('layouts.todo_navi')

{{-- template.blade.phpの@yield('content')に渡す --}}
@section('content')
  <div id="wrapper" class="col-12 col-sm-12 col-md-9 col-xl-10">
    <div class="py-3">
      {{-- ToDo作成ボタン --}}
      <a href="/todo/create" class="btn btn-primary">ToDo作成</></a>
      @if($search)
        <a class="pl-2 text-muted">検索ワード：{{ $search }}</a>
      @endif
      {{-- 件数表示 --}}
      <a class="pl-2 text-muted">{{ $todos->total() }} 件</a>
    </div>

    {{-- ToDoが一つもない場合はエラーを表示 --}}
    @if(count($todos)==0)
      <h5>ToDoがまだありません。</h5>
    @else
      {{-- 未達成のToDoを表示 --}}
      @foreach($todos as $todo)
        <div class="card mb-2">
          <div class="card-body">
            <h4 class="card-title">{{$todo->title}}</h4>
            <p>{!! nl2br(e($todo->explanation)) !!}</p>
            <h6 class="card-subtitle mb-2 text-body">難易度：{{$todo->difficulty}}</h6>
            <h6 class="card-subtitle mb-2 text-body">重要度：{{$todo->importance}}</h6>

            {{-- 現在日時と目標日時の差によって期限までの日数を表示 --}}
            <?php
              if($todo->deadline == date("Y-m-d")):
                echo '<h6 class="card-subtitle mb-2 text-danger">本日期限</h6>';
              elseif( ($todo->deadline. " ". $todo->deadline_time) < date("Y-m-d H:i:s") ):
                echo '<h6 class="card-subtitle mb-2 text-danger">'. ((strtotime(date("Y-m-d")) - (strtotime($todo->deadline))) / (60*60*24)). "日経過</h6>";
              elseif( ($todo->deadline) < date("Y-m-d", strtotime('+4 day')) ):
                echo '<h6 class="card-subtitle mb-2 text-warning">あと'. (strtotime($todo->deadline) - strtotime(date("Y-m-d"))) / (60*60*24). "日</h6>";
              else:
                echo '<h6 class="card-subtitle mb-2 text-success">あと'. (strtotime($todo->deadline) - strtotime(date("Y-m-d"))) / (60*60*24). "日</h6>";
              endif;
            ?>
            {{-- 目標期限に時間を設定している場合は表示する(時間設定は任意) --}}
            @if($todo->deadline_time)
              <h6 class="card-subtitle mb-2 text-body">目標期限：{{$todo->deadline. " ". substr($todo->deadline_time, 0, 5)}}</h6>
            @else
              <h6 class="card-subtitle mb-2 text-body">目標期限：{{$todo->deadline}}</h6>
            @endif

            <h6 class="card-subtitle mb-2 text-body">作成日時：{{($todo->created_at)->format('Y-m-d H:i')}}</h6>
            {{-- フォルダ名を表示 --}}
            @foreach($folders as $folder)
              @if($folder->id == $todo->folder_id)
                <h6 class="card-subtitle mb-2 text-body">フォルダ名：{{ $folder->name }}</h6>
              @endif
            @endforeach

            {{-- 各種ボタン --}}
            <p><a href="/todo/complete_confirm/{{$todo->id}}" class="btn btn-success">達成</a></p>
            <a href="/todo/edit/{{$todo->id}}" class="card-link">修正</a>
            <a href="/todo/delete_confirm/{{$todo->id}}" class="card-link">削除</a>
          </div>
        </div>
      @endforeach
      {{ $todos->links() }}
    @endif
  </div>

  {{-- サイドバー --}}
  <div class="col-12 col-sm-12 col-md-3 col-xl-2 order-md-first" style="background-color: #e3f2fd;">
    <ul class="list-group">
      <h4 class="pt-4 pb-2 pl-5 font-weight-bold">絞り込み</h4>
      <a href="/index/refine/all" class="list-group-item list-group-item-action font-weight-bold <?php if(session('refine')=='/'){ echo "active"; } ?>">未達成一覧</a>
      <a href="/index/refine/duesoon" class="list-group-item list-group-item-action font-weight-bold <?php if(session('refine')=='/duesoon'){ echo "active"; } ?>">期限間近</a>
      <a href="/index/refine/overdue" class="list-group-item list-group-item-action font-weight-bold <?php if(session('refine')=='/overdue'){ echo "active"; } ?>">期限超過</a>
    </ul>
    <ul class="list-group">
      <h4 class="pt-4 pb-2 pl-5 font-weight-bold">並べ替え</h4>
      <a href="/index/sort/created_at" class="list-group-item list-group-item-action font-weight-bold <?php if(session('sort')=='created_at'){ echo "active"; } ?>">作成日時 <?php if(session('sort')=='created_at'){ echo '[' . session('order') . ']'; } ?></a>
      <a href="/index/sort/deadline" class="list-group-item list-group-item-action font-weight-bold <?php if(session('sort')=='deadline'){ echo "active"; } ?>">期限 <?php if(session('sort')=='deadline'){ echo '[' . session('order') . ']'; } ?></a>
      <a href="/index/sort/difficulty" class="list-group-item list-group-item-action font-weight-bold <?php if(session('sort')=='difficulty'){ echo "active"; } ?>">難易度 <?php if(session('sort')=='difficulty'){ echo '[' . session('order') . ']'; } ?></a>
      <a href="/index/sort/importance" class="list-group-item list-group-item-action font-weight-bold <?php if(session('sort')=='importance'){ echo "active"; } ?>">重要度 <?php if(session('sort')=='importance'){ echo '[' . session('order') . ']'; } ?></a>
    </ul>
  </div>
@endsection