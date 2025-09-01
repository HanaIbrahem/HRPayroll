<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public function render()
    {
        $role=Auth::user()->role;
      
   
        return view('livewire.dashboard',compact('role'));
    }
}
