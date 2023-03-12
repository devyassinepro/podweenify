@include('shared/header')
    <div class="container-fluid">
      <div class="row">
      @include('shared/navigation')

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

          <h2>Add Store</h2>
          @if(session('status'))
        <div class="alert alert-success mb-1 mt-1">
            {{ session('status') }}
        </div>
        @endif
        <form action="{{ route('stores.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <div class="form-group">
                        <strong>Store store:</strong>
                        <input type="text" name="url" class="form-control" placeholder="Store">
                        @error('store')
                        <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary ml-3">Submit</button>
            </div>
        </form>
          </div>
          <div>
        </div>
        </main>
      </div>
    </div>
    
    @include('shared/footer')
