<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;

use Illuminate\Support\Facades\Storage;

use App\Http\Requests\PostRequest;

class PostController extends Controller
{

     //Mostrar una lista de Post
    public function index()
    {
        return view('admin.posts.index');
    }

    //Mostrar el formulario pora crear un nuevo post
    public function create()
    {
        $categories = Category::pluck('name', 'id');
        $tags = Tag::all();

        return view('admin.posts.create', compact('categories', 'tags'))->with('info', 'El post se creò con éxito');
    }

  
    //Almacenan los registros recien creados en la base de datos
    public function store(PostRequest $request)
    {   
        /* return Storage::put('posts', $request->file('file')); */

        $post = Post::create($request->all());

        if($request->file('file')){
            $url = Storage::put('posts', $request->file('file'));
            $post->image()->create([
                'url' => $url
            ]);
        }
        if($request->tags){
            $post->tags()->attach($request->tags);
        }
        return redirect()->route('admin.posts.edit', $post);
    }

    //Mostramos un registro especifico
    public function show(Post $post)
    {
        return view('admin.posts.show', compact('post'));
    }

  
    //Muestra el formulario con los datos a editar de un registro especifico
    public function edit(Post $post)
    {
        $this->authorize('author', $post); //policy de seguridad

        $categories = Category::pluck('name', 'id');
        $tags = Tag::all();

        return view('admin.posts.edit', compact('post', 'categories', 'tags'));
    }

 
    //Actualizar un registro en la base de datos
    public function update(PostRequest $request, Post $post)
    {
        $this->authorize('author', $post);//policy de seguridad

        $post->update($request->all());

        if ($request->file('file')){
            $url = Storage::put('posts', $request->file('file'));

            if($post->image){
                Storage::delete($post->image->url);

                $post->image->update([
                    'url' => $url
                ]);
            }else{
                $post->image()->create([
                    'url' => $url
                ]);
            }
        }
        if($request->tags){
            $post->tags()->sync($request->tags);
        }
        return redirect()->route('admin.posts.edit', $post)->with('info', 'El post se actualizo con éxito');
    }

 
    //Eliminar un registro especifico de la base de datos
    public function destroy(Post $post)
    {
        $this->authorize('author', $post);//policy de seguridad

        $post->delete();
        return redirect()->route('admin.posts.index')->with('info', 'El post se elimino con éxito');
    }
}
