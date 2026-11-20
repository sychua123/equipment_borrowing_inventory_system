{{-- Author: Lai Chee Kit · Module 4: the fields shared by "submit ticket" and "edit ticket" --}}
{{-- Needs: $ticket, $equipmentList, $equipmentNotice --}}

@if ($equipmentNotice)
  <p class="warn">{{ $equipmentNotice }}</p>
@endif

<label>Title
  <input name="title" maxlength="120" required value="{{ old('title', $ticket->subject) }}">
  @error('title') <small class="error">{{ $message }}</small> @enderror
</label>

<label>Category
  <select name="category" required>
    @foreach (\App\Models\Ticket::CATEGORIES as $category)
      <option value="{{ $category }}" @selected(old('category', $ticket->category) === $category)>{{ $category }}</option>
    @endforeach
  </select>
  @error('category') <small class="error">{{ $message }}</small> @enderror
</label>

<label>Equipment
  <select name="equipment_id">
    <option value="">No equipment</option>
    @foreach ($equipmentList as $equipment)
      <option value="{{ $equipment['id'] }}" @selected((string) old('equipment_id', $ticket->equipment_id) === (string) $equipment['id'])>
        {{ $equipment['asset_tag'] }} · {{ $equipment['name'] }}{{ $equipment['status'] === 'Retired' ? ' (retired)' : '' }}
      </option>
    @endforeach
  </select>
  <small class="hint">Required when the category is Fault.</small>
  @error('equipment_id') <small class="error">{{ $message }}</small> @enderror
</label>

<label>Priority
  <select name="priority" required>
    @foreach (\App\Models\Ticket::PRIORITIES as $priority)
      <option value="{{ $priority }}" @selected(old('priority', $ticket->priority ?? 'medium') === $priority)>{{ \App\Models\Ticket::label($priority) }}</option>
    @endforeach
  </select>
  @error('priority') <small class="error">{{ $message }}</small> @enderror
</label>

<label>Description
  <textarea name="description" rows="6" maxlength="2000" required>{{ old('description', $ticket->description) }}</textarea>
  @error('description') <small class="error">{{ $message }}</small> @enderror
</label>

{{-- Edit only: photos already on the ticket, each with a "remove" box --}}
@if ($ticket->exists && $ticket->photos->isNotEmpty())
  <p>Current photos (tick to remove):</p>
  <div class="photos">
    @foreach ($ticket->photos as $photo)
      <label class="inline">
        <img src="{{ route('attachments.show', $photo) }}" alt="{{ $photo->file_name }}"><br>
        <input type="checkbox" name="remove_photos[]" value="{{ $photo->id }}" @checked(in_array($photo->id, (array) old('remove_photos', [])))>
        Remove
      </label>
    @endforeach
  </div>
  @error('remove_photos.*') <small class="error">{{ $message }}</small> @enderror
@endif

<label>{{ $ticket->exists ? 'Add photos' : 'Photos (optional)' }}
  <input type="file" name="photos[]" accept=".jpg,.jpeg,.png" multiple>
  <small class="hint">JPG or PNG, up to 2 MB each, at most {{ config('module4.max_photos') }} photos per ticket.</small>
  @error('photos') <small class="error">{{ $message }}</small> @enderror
  @error('photos.*') <small class="error">{{ $message }}</small> @enderror
</label>
