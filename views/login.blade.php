@extends($view['layout'])
@section($view['section'])
<div style="position:absolute;top:0;bottom:0;left:0;right:0;margin:auto;height:400px;width:300px;">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{ $view['title'] }}</h3>
        </div>
        <div class="panel-body">
            <p>{{ $view['prompt'] }}</p>
            @foreach($errors->all() as $message)
            <p class="alert alert-danger">{{ $message }}</p>
            @endforeach
            <form accept-charset="UTF-8" role="form" action="{{ URL::route('lock.login') }}" method="POST">
            <fieldset>
                <div class="form-group">
                    <input class="form-control" placeholder="Username" name="username" type="text" value="{{ Input::old('username', '') }}">
                </div>
                <div class="form-group">
                    <input class="form-control" placeholder="Password" name="password" type="password" value="">
                </div>
                <input class="btn btn-lg btn-success btn-block" type="submit" value="Login">
            </fieldset>
            </form>
        </div>
    </div>
</div>
@stop