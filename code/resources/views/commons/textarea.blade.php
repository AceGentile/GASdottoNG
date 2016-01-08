<div class="form-group">
	@if($squeeze == false)
	<label for="{{ $prefix . $name . $postfix }}" class="col-sm-3 control-label">{{ $label }}</label>
	@endif

	<div class="col-sm-{{ $fieldsize }}">
		<textarea
			class="form-control"
			name="{{ $prefix . $name . $postfix }}"

			@if($squeeze == true)
			placeholder="{{ $label }}"
			@endif

			autocomplete="off"><?php if($obj) echo $obj->$name ?></textarea>
	</div>
</div>
