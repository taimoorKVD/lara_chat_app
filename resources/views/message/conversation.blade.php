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
                        <li class="list-group-item chat-user-list @if($user->id == $friendInfo->id) active @endif">
                            <a href="{{ route('message.conversation', $user->id) }}" class="text-secondary">
                                <div class="chat-image">
                                    {!! makeImageFromUserName($user->name) !!}
                                    <i class="fa fa-circle user-status-icon" title="away"></i>
                                </div>
                                <div class="chat-name font-weight-bold">
                                    {{ $user->name }}
                                </div>
                            </a>
                        </li>
                        @endforeach
                        @else
                        <li class="chat-user-list">
                            <a href="javascript:void(0)" class="text-secondary">
                                No user found
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
            
            <div class="col-md-9 mt-4">
                <div class="card">
                    <div class="card-body">
                        <div class="chat-header">
                            <a href="{{ route('message.conversation', $user->id) }}" class="text-secondary">
                                <div class="chat-image">
                                    {!! makeImageFromUserName($user->name) !!}
                                </div>
                                <div class="chat-name font-weight-bold">
                                    {{ $user->name }}
                                    <i class="fa fa-circle user-status-icon-1 user-icon-{{ $user->id }}" title="away" id="userStatushead{{ $friendInfo->id }}"></i>
                                </div>
                            </a>
                        </div>
                        <hr class="text-secondary">
                        <div class="chat-body" id="chatBody">
                            <div class="message-listing" id="messageWrapper">
                                {{-- <div class="row message align-items-center mb-2"> --}}
                                    
                                    {{-- <div class="col-md-12 user-info">
                                        <div class="chat-image">
                                            {!! makeImageFromUserName('Peter Parker') !!}
                                        </div>
                                        <div class="chat-name font-weight-bold">
                                            Peter parker
                                            <span class="small time text-gray-500" title="2021-10-7 06:00 PM">
                                                06:00 PM
                                            </span>
                                        </div>
                                    </div> --}}
                                    
                                    {{-- <div class="col-md-12 message-content">
                                        <div class="message-text">
                                            message here...
                                        </div>
                                    </div> --}}

                                {{-- </div> --}}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="chat-box">
                            <div class="chat-input bg-white" id="chatInput" contenteditable="">
                                
                            </div>
                            <div class="chat-input-toolbar">
                                <button title="Add File" class="btn btn-light btn-sm btn-file-upload tool-items">
                                    <i class="fa fa-paperclip"></i>
                                </button> | 
                                <button title="Bold" class="btn btn-light btn-sm tool-items" onclick="document.execCommand('bold', false, '');">
                                    <i class="fa fa-bold tool-icon"></i>
                                </button> | 
                                <button title="Italic" class="btn btn-light btn-sm tool-items" onclick="document.execCommand('italic', false, '');">
                                    <i class="fa fa-italic tool-icon"></i>
                                </button> | 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script>
        $(function() {

            let chatInput = $('.chat-input');
            let chatInputToolbar = $('.chat-input-toolbar');
            let chatBody = $('.chat-body');
            let messageWrapper = $('#messageWrapper');

            let user_id = "{{ auth()->user()->id }}";
            let friend_id = "{{ $friendInfo->id }}";
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

            chatInput.keypress(function(e) {
                let message = $(this).html();
                if(e.which === 13 && !e.shiftKey) {
                    chatInput.html("");
                    sendMessage(message);
                    return false;
                }
            });

            function sendMessage(message) {
                let url = "{{ route('message.sendMessage') }}";
                let form = $(this);
                let formData = new FormData();
                let token = "{{ csrf_token() }}";

                formData.append('message', message);
                formData.append('_token', token);
                formData.append('receiver_id', friend_id);

                appendMessageToSender(message);

                $.ajax({
                    url: url,
                    type: "post",
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: "JSON",
                    success: function(response) {
                        if(response.success) {
                            console.log(response.data);
                        }
                    },
                });
            }

            // show message to sender //
            function appendMessageToSender(message) {
                let name = "{{ $myInfo->name }}";
                let image = "{!! makeImageFromUserName($myInfo->name) !!}";

                let userInfo = '<div class="col-md-12 user-info">' +
                                    '<div class="chat-image">' +
                                        image +
                                    '</div>' +
                                    '<div class="chat-name font-weight-bold">' + 
                                        name +
                                        '<span class="small time text-gray-500" title="' + getCurrentDateTime() + '">' 
                                            + ' '+ getCurrentTime() +
                                        '</span>' +
                                    '</div>' +
                                '</div>';
                
                let messageContent = '<div class="col-md-12 message-content">' +
                                        '<div class="message-text">' 
                                            + message +
                                        '</div>' +
                                     '</div>';

                let newMessage = '<div class="row message align-items-center mb-2">' 
                                    + userInfo + messageContent +
                                 '</div>';
                messageWrapper.append(newMessage);
            }

            // show message to receiver //
            function appendMessageToReceiver(message) {
                let name = "{{ $friendInfo->name }}";
                let image = "{!! makeImageFromUserName($friendInfo->name) !!}";

                let userInfo = '<div class="col-md-12 user-info">' +
                                    '<div class="chat-image">' +
                                        image +
                                    '</div>' +
                                    '<div class="chat-name font-weight-bold">' + 
                                        name +
                                        '<span class="small time text-gray-500" title="' + dateFormat(message.created_at) + '">' 
                                            + ' '+ timeFormat(message.created_at) +
                                        '</span>' +
                                    '</div>' +
                                '</div>';
                
                let messageContent = '<div class="col-md-12 message-content">' +
                                        '<div class="message-text">' 
                                            + message.content +
                                        '</div>' +
                                     '</div>';

                let newMessage = '<div class="row message align-items-center mb-2">' 
                                    + userInfo + messageContent +
                                 '</div>';
                messageWrapper.append(newMessage);
            }

            socket.on("private-channel:App\\Events\\PrivateMessageEvent", function(message) {
                appendMessageToReceiver(message);
            });
        });
    </script>
@endpush
