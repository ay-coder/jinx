<div class="box-body">
    <div class="form-group">
        {{ Form::label('ghost_mode', 'Ghost Mode :', ['class' => 'col-lg-2 control-label']) }}
        <div class="col-lg-10">
            {{ Form::text('ghost_mode', null, ['class' => 'form-control', 'placeholder' => 'Ghost Mode', 'required' => 'required']) }}
        </div>
    </div>
</div><div class="box-body">
    <div class="form-group">
        {{ Form::label('interested', 'Interested :', ['class' => 'col-lg-2 control-label']) }}
        <div class="col-lg-10">
            {{ Form::text('interested', null, ['class' => 'form-control', 'placeholder' => 'Interested', 'required' => 'required']) }}
        </div>
    </div>
</div><div class="box-body">
    <div class="form-group">
        {{ Form::label('age_start_range', 'Age Start Range :', ['class' => 'col-lg-2 control-label']) }}
        <div class="col-lg-10">
            {{ Form::text('age_start_range', null, ['class' => 'form-control', 'placeholder' => 'Age Start Range', 'required' => 'required']) }}
        </div>
    </div>
</div><div class="box-body">
    <div class="form-group">
        {{ Form::label('age_end_range', 'Age End Range :', ['class' => 'col-lg-2 control-label']) }}
        <div class="col-lg-10">
            {{ Form::text('age_end_range', null, ['class' => 'form-control', 'placeholder' => 'Age End Range', 'required' => 'required']) }}
        </div>
    </div>
</div><div class="box-body">
    <div class="form-group">
        {{ Form::label('distance', 'Distance :', ['class' => 'col-lg-2 control-label']) }}
        <div class="col-lg-10">
            {{ Form::text('distance', null, ['class' => 'form-control', 'placeholder' => 'Distance', 'required' => 'required']) }}
        </div>
    </div>
</div>