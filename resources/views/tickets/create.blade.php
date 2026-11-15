{{-- Author: Lai Chee Kit · Module 4: submit ticket form --}}
@extends('layouts.app')
@section('title', 'Submit a Ticket')

@section('content')
  <h1>Submit a Ticket</h1>

  <form method="POST" action="{{ route('tickets.store') }}">
    @csrf

    <label>Title
      <input name="title" maxlength="120" required value="{{ old('title') }}">
      @error('title') <small class="error">{{ $message }}</small> @enderror
    </label>

    <label>Category
      <select name="category" required>
        @foreach ($categories as $category)
          <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
        @endforeach
      </select>
      @error('category') <small class="error">{{ $message }}</small> @enderror
    </label>

    <label>Equipment
      <select name="equipment_id">
        <option value="">No equipment</option>
        @foreach ($equipmentList as $equipment)
          <option value="{{ $equipment['id'] }}" @selected((string) old('equipment_id') === (string) $equipment['id'])>
            {{ $equipment['asset_tag'] }} · {{ $equipment['name'] }}
          </option>
        @endforeach
      </select>
      <small class="hint">Required when the category is Fault.</small>
      @error('equipment_id') <small class="error">{{ $message }}</small> @enderror
    </label>

    <label>Priority
      <select name="priority" required>
        @foreach ($priorities as $priority)
          <option value="{{ $priority }}" @selected(old('priority', 'Medium') === $priority)>{{ $priority }}</option>
        @endforeach
      </select>
      @error('priority') <small class="error">{{ $message }}</small> @enderror
    </label>

    <label>Description
      <textarea name="description" rows="6" maxlength="2000" required>{{ old('description') }}</textarea>
      @error('description') <small class="error">{{ $message }}</small> @enderror
    </label>

    <button type="submit">Submit ticket</button>
  </form>
@endsection
