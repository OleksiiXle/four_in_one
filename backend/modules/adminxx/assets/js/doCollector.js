var collector={
  runner:[],
  counter:0,
  def : null,
  countDone:function(){--collector.counter; if (collector.counter == 0) collector.def.resolve();},
  run:function(setup){
    this.def = jQuery.Deferred();
    this.counter = setup.length;//Object.keys(setup).length;
    $.each(setup,function(index,obj){console.log(typeof(obj));
      if (typeof(obj) == 'object')
        collector.runner.push($.get.apply($.get,obj).done(collector.countDone)); else
      if (typeof(obj) == 'function')
        collector.countDone();
    });
    return this.def.promise();
  },
  clear:function (){
    this.runner = [];
    this.def = null;
  }
}
/*
{
//вариант 1 (функция)
  function (){
//делаешь что хочешь
  },
//вариант второй - внутренности get-запроса
  {'/complect/suborders/person',{
    'id':$('#personmove-person').val()
    ,'SAGNRANK':'oi3'
    ,'fio':'oa3'
    ,'spec_document':''
    ,'sex':''}
      ,function(data,status){
    if (data != ''){
      var jsn = $.parseJSON(data);
      res['person'] = 'Призначити '+$.trim(jsn.SAGNRANK+' '+jsn.fio);
      if (jsn.spec_document != '')
        res['person'] += ' ('+jsn.spec_document+')';
      var resC = $('#suborders-title');
      if (resC.css('background-color') == 'rgb(238, 255, 255)')
        resC.val(res['person']);
      res['_sex'] = jsn.sex==1?'йому':jsn.sex==2?'їй':'цьому';
    }
  }}
}

*/