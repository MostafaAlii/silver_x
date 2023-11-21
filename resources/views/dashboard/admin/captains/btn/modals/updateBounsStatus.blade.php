<div class="modal fade" id="editStatus{{$bouns->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ trans('general.edit') .' Bouns Status '}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('captains.update-bouns-status', $bouns->id, $bouns->captain_id)}}" method="POST">
                    @csrf
                    <!-- Start Status Status -->
                    <div class="p-1 form-group">
                        <label for="status">Status</label>
                        <select name="status" class="p-1 form-control">
                            <option selected disabled>Select <span class="text-primary">{{$bouns->name}}</span>
                                Status...</option>
                            <option value="active" {{ (old('status', $bouns->status) == 'active') ? 'selected' : '' }}>
                                {{ trans('general.active') }}
                            </option>
                            <option value="inactive" {{ (old('status', $bouns->status) == 'inactive') ? 'selected' : ''}}>
                                {{ trans('general.inactive') }}
                            </option>
                            <option value="waiting" {{ (old('status', $bouns->status) == 'waiting') ? 'selected' : ''}}>
                                {{ trans('general.waiting') }}
                            </option>
                        </select>
                        @error('status')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- End Status Selected -->

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('general.close')
                            }}</button>
                        <button type="submit" class="btn btn-success">{{ trans('general.edit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>