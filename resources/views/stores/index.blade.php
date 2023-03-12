@include('shared/header')
    <div class="container-fluid">
      <div class="row">
      @include('shared/navigation')

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">

          <h2>List Stores</h2><h4>Total Stores : {{$totalstores}}</h4>
          <a class="btn btn-success" href="{{ route('stores.create') }}">Add</a>
</br></br>
          @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif
        <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Store</th>
                    <th>Start Tracking</th>
                    <th>Status</th>
                    <th>Revenue</th>
                    <th>Sales</th>
                    <th width="280px">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($stores as $store)
                    <tr>
                        <td><a href="{{ route('product.show',$store->id) }}">{{ $store->url }}</a></td>
                        <td>{{ $store->created_at }}</td>
                        <td>{{ $store->status }}</td>
                        <td>{{ $store->Revenue }} $</td>
                        <td>{{ $store->Sales }}</td>
                        <td>
                            <form action="{{ route('stores.destroy',$store->id) }}" method="Post">
                                <!-- <a class="btn btn-primary" href="{{ route('stores.edit',$store->id) }}">Edit</a> -->
                                <a class="btn btn-primary" href="{{ route('product.show',$store->id) }}">Show</a>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
            </tbody>
            </table>
          </div>

          <div>
        <!-- {{ $stores->links() }}  -->
        {{  $stores->links() }}

        </div>
        </main>
      </div>
    </div>
    
    @include('shared/footer')
