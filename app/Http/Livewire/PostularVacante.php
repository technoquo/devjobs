<?php

namespace App\Http\Livewire;

use App\Models\Vacante;
use Livewire\Component;
use App\Models\Candidato;
use Livewire\WithFileUploads;
use App\Notifications\NuevoCandidato;

class PostularVacante extends Component
{

    use WithFileUploads;

    public $cv;
    public $vacante;

    protected $rules = [
      'cv' => 'required|mimes:pdf'
    ];


    public function mount(Vacante $vacante)
    {
        $this->vacante = $vacante;
    }

    public function postularme()
    {
        $datos = $this->validate();


        if($this->vacante->candidatos()->where('user_id', auth()->user()->id)->count() > 0) {

            session()->flash('error', 'Ya postulaste a esta vacante anteriormente');

        } else {

       // Almacenar CV en el disco duro
       $cv = $this->cv->store('public/cv');      
       $datos['cv'] = str_replace('public/cv/', '', $cv);
       

       // Crear la vacante
      $this->vacante->candidatos()->create([
        'user_id' => auth()->user()->id ,       
        'cv' =>  $datos['cv']      
    ]);

       //Crear notificacion y enviar el email
           $this->vacante->reclutador->notify(new NuevoCandidato($this->vacante->id, $this->vacante->titulo, auth()->user()->id));

       // Mostrar el usuario un mensaje de OK
           session()->flash('mensaje', 'Se envió correctamente tu información, mucha suerte');

           return redirect()->back();
       }

    }

    public function render()
    {
        return view('livewire.postular-vacante');
    }
}
