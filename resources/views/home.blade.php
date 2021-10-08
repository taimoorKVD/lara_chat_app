@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="row chat-row">
        
            <div class="col-md-3">
                <div class="users">
                    <h5>Users</h5>
                    <ul class="list-group list-chat-item">
                        @if($users->count() > 0)
                        @foreach($users as $user)
                        <li class="list-group-item chat-user-list">
                            <a href="{{ route('message.conversation', $user->id) }}" class="text-secondary">
                                <div class="chat-image">
                                    {!! makeImageFromUserName($user->name) !!}
                                    <i class="fa fa-circle user-status-icon user-icon-{{ $user->id }}" title="away"></i>
                                </div>
                                <div class="chat-name font-weight-bold">
                                    {{ $user->name }}
                                </div>
                            </a>
                        </li>
                        @endforeach
                        @else
                        <li class="chat-user-list font-weight-bold">
                            <a href="javascript:void(0)" class="text-secondary">
                                No user found
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
            <div class="col-md-1 border-right"></div>
            <div class="col-md-8">
                <h1>
                    Message Section
                </h1>
                <p class="lead">
                    Select user from the list to begin conversation.
                </p>
            </div>
    
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script>
        $(function() {
            let user_id = "{{ auth()->user()->id }}";
            let ip_address = "127.0.0.1"
            let socket_port = "8001";
            let socket = io(ip_address + ":" + socket_port);

            socket.on('connect', function(){
                socket.emit('user_connected', user_id);
            });

            socket.on('updateUserStatus', (data) => {
                
                let userStatusIcon = $('.user-status-icon');
                userStatusIcon.css('color', 'lightgray');
                userStatusIcon.attr('title', 'away');

                $.each(data, function(key, val) {
                    if(val != null && val != 0) {
                        console.log(key);
                        let userIcon = $('.user-icon-' + key);
                        userIcon.css('color', 'greenyellow');
                        userIcon.attr('title', 'Online');
                    }
                });
                
            });
        });
    </script>
@endpush
