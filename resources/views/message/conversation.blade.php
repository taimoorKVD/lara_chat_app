@extends('layouts.app')

@section('content')
<style>
    .select2-container {
        width: 100% !important;
    }    
</style>
<div class="container">
    <div class="card">
        <div class="row chat-row">
        
            <div class="col-md-3">
                <div class="users">
                    <h5>Users</h5>
                    <ul class="list-group list-chat-item" style="margin-top: -5px;">
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
                    @if($users->count() > 0)
                        <p class="text-center">
                            {{ $users->links() }}
                        </p>
                    @endif
                </div>

                <div class="groups mt-5">
                    <h5>
                        Groups
                        <i class="fa fa-plus btn-add-group"></i>
                    </h5>
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

<div class="modal fade" id="addGroupModal" tabindex="-1" role="dialog" aria-labelledby="addGroupModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Create new group</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="">
            <div class="modal-body">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" namme="name" id="name" placeholder="Enter group name..">
                </div>
                <div class="form-group">
                    <label for="select-group-members">Group members</label>
                    <select class="form-control select-group-members" id="select-group-members" multiple="multiple" name="user_ids[]">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary">Create</button>
            </div>
        </form>
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

            $(document).on("click", ".btn-add-group", function() {
                jQuery("#addGroupModal").modal("show");
            });

            jQuery("#select-group-members").select2();
        });
    </script>
@endpush
