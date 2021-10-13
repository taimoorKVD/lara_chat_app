<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\MessageGroup;
use App\Models\MessageGroupMember;

use Illuminate\Http\Request;

class MessageGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required||unique:message_group,name'
        ]);

        $messageGroup = auth()->user()->groups()->create([
            'name' => $request->name
        ]);

        if($messageGroup) {
            if(isset($request->user_ids) && !empty($request->user_ids)) {
                foreach($request->user_ids as $user_id) {
                    $member_data['user_id'] = $user_id;
                    $member_data['message_group_id'] = $messageGroup->id;
                    $member_data['status'] = 0;
                    
                    MessageGroupMember::create($member_data);
                }
            }
        }
        
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->data['users'] = User::where('id', '!=', auth()->user()->id)
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        $this->data['currentGroup'] = MessageGroup::where('id', $id)
            ->with('message_group_member.user')
            ->get()
            ->first();
        $this->data['groups'] = MessageGroup::paginate(10);
        $this->data['myInfo'] = User::findOrFail(auth()->user()->id);
        return view('message_group.index', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
