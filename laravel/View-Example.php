@extends('themes.base')
@section('body')
  <div class="py-3">
    <h1>Content Section</h1>
    <form method="POST" action="{{ route('section-update', [$page->id,$id]) }}" enctype="multipart/form-data">
      <input name="_method" type="hidden" value="PATCH">
      @csrf
      <input type="hidden" name="page" value="{{ $page->id }}">
      <input type="hidden" name="slug" value="{{ $page->slug }}">
      <input type="hidden" name="type" value="{{ $content['type'] }}">
      <input type="hidden" name="id" value="{{ $content['id'] }}">
      <input type="hidden" name="style" value="{{ $content['style'] }}">
      <input type="hidden" name="order" value="{{ $content['order'] }}">
      <div class="form-group">
        <label for="content">Display</label>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="display" id="display_no" value="0" @if ( old('display', $content['display']) == 0) checked @endif>
          <label class="form-check-label" for="display_no">No</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="display" id="display_yes" value="1" @if ( old('display', $content['display']) == 1) checked @endif>
          <label class="form-check-label" for="display_yes">Yes</label>
        </div>
      </div>
      <div class="form-group">
        <label for="content">Label</label>
        <input type="text" name="label" value="{{ old('label', $content['label']) }}">
      </div>
      <div class="form-group">
        <label for="content">Content</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $data['description']) }}</textarea>
      </div>
      <div class="form-group">
        <label for="content">Files</label>
        <input type="file" name="photos[]" multiple /><br/>
      </div>
      <div class="form-group">
        <button type="submit" id="submit-all" class="btn btn-primary">Submit</button>
        <a 
          class="btn btn-outline-danger" 
          href="{{ route('section-destroy', [$page->id,$id,$content['type']]) }}" 
          onclick="event.preventDefault(); document.getElementById('destroy-section-form').submit();" role="button">Delete</a>
      </div>
    </form>
    <form id="destroy-section-form" action="{{ route('section-destroy', [$page->id,$id,$content['type']]) }}" method="POST" style="display: none;">
        <input name="_method" type="hidden" value="DELETE">
        @csrf
    </form>
  </div>
@endsection
