<div class="box-body">
    <div class="form-group">
        {{ Form::label('user_id', 'User Id :', ['class' => 'col-lg-2 control-label']) }}
        <div class="col-lg-10">
            {{ Form::text('user_id', null, ['class' => 'form-control', 'placeholder' => 'User Id', 'required' => 'required']) }}
        </div>
    </div>
</div><div class="box-body">
    <div class="form-group">
        {{ Form::label('other_user_id', 'Other User Id :', ['class' => 'col-lg-2 control-label']) }}
        <div class="col-lg-10">
            {{ Form::text('other_user_id', null, ['class' => 'form-control', 'placeholder' => 'Other User Id', 'required' => 'required']) }}
        </div>
    </div>
</div><div class="box-body">
    <div class="form-group">
        {{ Form::label('message', 'Message :', ['class' => 'col-lg-2 control-label']) }}
        <div class="col-lg-10">
            {{ Form::text('message', null, ['class' => 'form-control', 'placeholder' => 'Message', 'required' => 'required']) }}
        </div>
    </div>
</div><div class="box-body">
    <div class="form-group">
        {{ Form::label('is_read', 'Is Read :', ['class' => 'col-lg-2 control-label']) }}
        <div class="col-lg-10">
            {{ Form::text('is_read', null, ['class' => 'form-control', 'placeholder' => 'Is Read', 'required' => 'required']) }}
        </div>
    </div>
</div>