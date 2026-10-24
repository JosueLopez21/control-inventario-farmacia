<?php

namespace App\Support;

/**
 * Banco de preguntas sencillas para la ruleta, pensadas para el público general
 * (sin conocimientos de medicamentos). Cada pregunta:
 * [enunciado, respuesta correcta, [tres respuestas incorrectas], explicación].
 *
 * Son datos educativos generales: no reemplazan la consulta con un médico o farmacéutico.
 */
class PreguntasJuego
{
    /**
     * @return array<string, array<int, array{0: string, 1: string, 2: array<int, string>, 3: string}>>
     */
    public static function banco(): array
    {
        return [
            'analgesicos' => [
                ['¿Para qué se usa principalmente el paracetamol?', 'Para el dolor y la fiebre', ['Para la tos seca', 'Para dormir mejor', 'Para bajar de peso'], 'El paracetamol es un analgésico: calma el dolor y baja la fiebre.'],
                ['Un analgésico sirve para...', 'Aliviar el dolor', ['Combatir bacterias', 'Dar más vitaminas', 'Bajar la presión'], 'Analgésico significa "contra el dolor".'],
                ['El "Sorojchi pills", muy conocido en La Paz, se usa para...', 'El malestar y dolor de cabeza por la altura', ['Las lombrices', 'La tos con flema', 'Las caries'], 'El soroche es el malestar por la altura; es muy común al llegar a ciudades altas.'],
                ['Si un dolor dura varios días, lo mejor es...', 'Consultar al médico', ['Tomar el doble de pastillas', 'Cambiar de pastilla cada día', 'No hacer nada y esperar'], 'Un dolor que no pasa necesita revisión médica; nunca se debe duplicar la dosis.'],
                ['¿Qué aparato se usa para medir la fiebre?', 'El termómetro', ['La balanza', 'El tensiómetro', 'El glucómetro'], 'El termómetro mide la temperatura del cuerpo.'],
                ['La dosis de un medicamento para un niño se pregunta a...', 'Un médico o farmacéutico', ['Un vecino', 'Un video de internet', 'Cualquier persona que pase'], 'Los niños necesitan dosis exactas según su peso y edad.'],
            ],
            'antibioticos' => [
                ['Los antibióticos sirven para combatir...', 'Infecciones por bacterias', ['El dolor de cabeza', 'La falta de vitaminas', 'La gripe causada por virus'], 'Los antibióticos actúan contra bacterias, no contra virus.'],
                ['¿Se pueden tomar antibióticos sin indicación médica?', 'No, solo si los indica un médico', ['Sí, siempre', 'Sí, si un amigo los usó', 'Solo los fines de semana'], 'Usarlos mal crea bacterias resistentes y el tratamiento deja de funcionar.'],
                ['Si te recetan antibiótico por 7 días y al tercero te sientes mejor...', 'Completas el tratamiento indicado', ['Lo dejas ahí', 'Guardas el resto para después', 'Se lo das a un familiar'], 'Hay que terminar el tratamiento aunque ya te sientas bien.'],
                ['La amoxicilina es un...', 'Antibiótico', ['Jarabe para la tos', 'Crema para la piel', 'Suplemento de calcio'], 'La amoxicilina es uno de los antibióticos más conocidos.'],
                ['¿Un antibiótico cura la gripe común?', 'No, la gripe la causa un virus', ['Sí, siempre', 'Solo en invierno', 'Solo si es un jarabe'], 'Los antibióticos no sirven contra virus como el de la gripe.'],
                ['Antes de recibir un antibiótico, avisa al médico si...', 'Eres alérgico a algún medicamento', ['Te gusta el fútbol', 'Tienes zapatos nuevos', 'Es lunes'], 'Las alergias a medicamentos pueden ser graves, por eso se avisan siempre.'],
            ],
            'antiinflamatorios' => [
                ['El ibuprofeno sirve para...', 'Bajar la inflamación y el dolor', ['Dar sueño', 'Quitar el hambre', 'Subir la presión'], 'El ibuprofeno es un antiinflamatorio muy conocido.'],
                ['Una parte inflamada del cuerpo se ve...', 'Hinchada, roja o caliente', ['Más liviana', 'Más fría y pálida', 'Transparente'], 'Hinchazón, enrojecimiento y calor son señales de inflamación.'],
                ['Muchos antiinflamatorios conviene tomarlos...', 'Después de comer', ['Con el estómago vacío', 'Junto con alcohol', 'Solo con café'], 'Con alimentos se cuida el estómago.'],
                ['Si te golpeas el tobillo y se hincha, al inicio ayuda...', 'Aplicar frío y reposar', ['Caminar rápido', 'Poner calor intenso', 'Masajear fuerte'], 'El frío ayuda a bajar la hinchazón en las primeras horas.'],
                ['El diclofenaco en gel se aplica...', 'Sobre la piel de la zona adolorida', ['Dentro del oído', 'En los ojos', 'En la boca'], 'Los geles son de uso externo, solo sobre la piel.'],
                ['Antes de usar antiinflamatorios deben consultar...', 'Personas con problemas de estómago o riñón', ['Solo los deportistas', 'Nadie', 'Solo quienes viajan'], 'En esos casos el médico decide si es seguro usarlos.'],
            ],
            'antihipertensivos' => [
                ['Los antihipertensivos ayudan a controlar...', 'La presión arterial alta', ['El azúcar en la sangre', 'La tos', 'La fiebre'], 'Hipertensión es el nombre de la presión arterial alta.'],
                ['¿Qué aparato mide la presión arterial?', 'El tensiómetro', ['El termómetro', 'La balanza', 'El glucómetro'], 'El tensiómetro mide la presión de la sangre.'],
                ['Para cuidar la presión conviene comer menos...', 'Sal', ['Verduras', 'Agua', 'Frutas'], 'Demasiada sal sube la presión arterial.'],
                ['Las pastillas para la presión se toman...', 'Todos los días, como indicó el médico', ['Solo cuando duele la cabeza', 'Solo en fiestas', 'Cuando uno se acuerda'], 'La presión alta se controla con constancia, aunque uno se sienta bien.'],
                ['La hipertensión se llama "enemigo silencioso" porque...', 'Muchas veces no da síntomas', ['Siempre duele mucho', 'Solo aparece de noche', 'Se contagia'], 'Por eso conviene medirse la presión con regularidad.'],
                ['Caminar un poco cada día ayuda a...', 'Cuidar el corazón y la presión', ['Subir de peso', 'Perder la memoria', 'Tener más sueño'], 'El ejercicio suave es bueno para el corazón.'],
            ],
            'antihistaminicos' => [
                ['Los antihistamínicos sirven para aliviar...', 'Alergias, como picazón y estornudos', ['La diarrea', 'El dolor de muelas', 'La presión alta'], 'Actúan contra los síntomas de la alergia.'],
                ['Un síntoma típico de la alergia es...', 'Estornudos y picazón', ['Fiebre de 40 grados', 'Hambre', 'Dolor de espalda'], 'Estornudar, picazón en ojos y nariz que gotea son señales de alergia.'],
                ['Algunos antihistamínicos pueden dar...', 'Sueño', ['Mucha energía', 'Hambre', 'Frío'], 'Por eso se debe tener cuidado al manejar.'],
                ['Si después de comer algo se hincha la cara y falta el aire, lo correcto es...', 'Ir de inmediato a emergencias', ['Tomar un mate y dormir', 'Esperar hasta mañana', 'Ignorarlo'], 'Una reacción alérgica fuerte es una urgencia médica.'],
                ['Una causa común de alergia es...', 'El polvo y el polen', ['Tomar agua', 'Dormir bien', 'Caminar'], 'El polvo y el polen provocan rinitis alérgica.'],
                ['La loratadina es un medicamento para...', 'Las alergias', ['Las infecciones', 'La diabetes', 'Las lombrices'], 'La loratadina es un antihistamínico muy usado.'],
            ],
            'antidiabeticos' => [
                ['En la diabetes, se tiene demasiada ___ en la sangre', 'Azúcar (glucosa)', ['Sal', 'Agua', 'Grasa'], 'La diabetes es el exceso de glucosa en la sangre.'],
                ['¿Qué aparato mide el azúcar en la sangre?', 'El glucómetro', ['El termómetro', 'El tensiómetro', 'La balanza'], 'Con una gotita de sangre, el glucómetro muestra el nivel de azúcar.'],
                ['Para cuidar la diabetes conviene tomar menos...', 'Gaseosas y dulces', ['Agua', 'Verduras', 'Té sin azúcar'], 'Los azúcares simples elevan la glucosa rápidamente.'],
                ['La metformina es un medicamento para...', 'La diabetes tipo 2', ['La tos', 'El dolor de oído', 'La anemia'], 'Es uno de los medicamentos más usados para controlar el azúcar.'],
                ['La insulina es una hormona que ayuda al cuerpo a usar...', 'El azúcar', ['La sal', 'El calcio', 'El oxígeno'], 'Sin insulina, el azúcar se queda en la sangre.'],
                ['Una actividad que ayuda a controlar el azúcar es...', 'Caminar todos los días', ['Estar todo el día sentado', 'Saltarse comidas', 'Dormir poco'], 'El ejercicio ayuda a que el cuerpo use mejor el azúcar.'],
            ],
            'vitaminas-y-suplementos' => [
                ['La vitamina C abunda en...', 'La naranja, la mandarina y el limón', ['El pan', 'El arroz', 'La carne'], 'Los cítricos son muy ricos en vitamina C.'],
                ['El calcio fortalece los huesos y está en...', 'La leche y el queso', ['El té', 'La gaseosa', 'El aceite'], 'Los lácteos son una buena fuente de calcio.'],
                ['El hierro ayuda a prevenir...', 'La anemia', ['La caspa', 'La tos', 'Las caries'], 'La anemia es la falta de hierro o glóbulos rojos en la sangre.'],
                ['La piel produce vitamina D con ayuda de...', 'La luz del sol', ['El frío', 'El agua', 'La luna'], 'Unos minutos de sol al día ayudan a producir vitamina D.'],
                ['¿Cuál de estos alimentos bolivianos es muy nutritivo y rico en proteínas?', 'La quinua', ['La gaseosa', 'El caramelo', 'Las papas fritas de bolsa'], 'La quinua es un grano andino muy completo.'],
                ['Un suplemento vitamínico...', 'Complementa una buena alimentación', ['La reemplaza por completo', 'Se debe tomar a puñados', 'Cura todas las enfermedades'], 'Las vitaminas ayudan, pero no sustituyen comer bien.'],
            ],
            'gastrointestinales' => [
                ['El omeprazol se usa para...', 'La acidez y el ardor de estómago', ['El dolor de oído', 'La tos', 'La fiebre'], 'Reduce el ácido que produce el estómago.'],
                ['Con diarrea, lo más importante es...', 'Tomar muchos líquidos', ['No tomar agua', 'Comer solo grasas', 'Hacer ejercicio fuerte'], 'La diarrea hace perder agua y hay que reponerla.'],
                ['Las sales de rehidratación oral sirven para...', 'Reponer agua y sales del cuerpo', ['Dar sueño', 'Bajar la presión', 'Matar bacterias'], 'Se disuelven en agua hervida y se toman a sorbos.'],
                ['Para evitar problemas de estómago, antes de comer hay que lavarse...', 'Las manos', ['El cabello', 'Los zapatos', 'Los dientes solamente'], 'Lavarse las manos evita muchas infecciones.'],
                ['Si la diarrea dura más de 2 o 3 días...', 'Hay que acudir al médico', ['Se toma más café', 'Se espera un mes', 'Se come más picante'], 'Si no mejora, puede haber deshidratación o infección.'],
                ['La acidez suele empeorar con comidas...', 'Muy picantes y grasosas', ['Hervidas', 'Con agua', 'Con verduras cocidas'], 'Las comidas fuertes irritan el estómago.'],
            ],
            'dermatologicos' => [
                ['Las cremas y pomadas se aplican...', 'Sobre la piel', ['Dentro de los oídos', 'En la comida', 'Dentro del ojo'], 'Son medicamentos de uso externo.'],
                ['El clotrimazol sirve contra...', 'Los hongos de la piel', ['El dolor de cabeza', 'La tos', 'La presión alta'], 'Se usa en pie de atleta y otras micosis.'],
                ['Antes de aplicar una crema conviene lavarse...', 'Las manos', ['Los zapatos', 'El cabello', 'Las orejas'], 'Así no se lleva suciedad a la piel.'],
                ['En ciudades altas como La Paz o El Alto, el sol es muy fuerte; por eso se recomienda...', 'Usar protector solar', ['Broncearse más rápido', 'Salir sin sombrero', 'Aplicar aceite'], 'En la altura la radiación solar es mayor y quema más rápido.'],
                ['Si una herida está sucia, lo primero es...', 'Lavarla con agua limpia y jabón', ['Taparla sin limpiar', 'Echarle tierra', 'Dejarla al aire sin lavarla'], 'Limpiar bien evita infecciones.'],
                ['La hidrocortisona en crema se usa para...', 'La picazón e irritación leve de la piel', ['El dolor de muelas', 'Las lombrices', 'La diarrea'], 'Es un antiinflamatorio suave para la piel.'],
            ],
            'broncodilatadores' => [
                ['Los broncodilatadores ayudan a respirar mejor en...', 'El asma', ['La gastritis', 'El dolor de cabeza', 'La diabetes'], 'Abren los conductos por donde pasa el aire.'],
                ['El salbutamol suele usarse con...', 'Un inhalador', ['Una venda', 'Una inyección en la rodilla', 'Un parche en la oreja'], 'El inhalador lleva el medicamento directo a los pulmones.'],
                ['El ambroxol se usa en la tos con flema para...', 'Aflojar la flema', ['Dar más energía', 'Bajar la fiebre', 'Calmar el dolor de muela'], 'Hace que la flema sea más fácil de expulsar.'],
                ['Los pulmones sirven para...', 'Respirar', ['Digerir los alimentos', 'Filtrar la sangre', 'Mover los brazos'], 'En ellos entra el oxígeno que necesita el cuerpo.'],
                ['El humo del cigarrillo daña principalmente...', 'Los pulmones', ['Las uñas', 'El cabello', 'Los dedos de los pies'], 'Fumar causa tos, asma y enfermedades graves.'],
                ['En ciudades de altura como La Paz hay menos...', 'Oxígeno en el aire', ['Gravedad', 'Luz', 'Agua'], 'Por eso al llegar uno se agita más al caminar.'],
            ],
            'antiparasitarios' => [
                ['Los antiparasitarios eliminan...', 'Lombrices y otros parásitos', ['Virus del resfrío', 'Dolores musculares', 'Alergias'], 'Los parásitos viven dentro del cuerpo, sobre todo en el intestino.'],
                ['El albendazol es muy usado para...', 'Desparasitar', ['Bajar la fiebre', 'Subir la presión', 'Calmar la tos'], 'Es el desparasitante más conocido.'],
                ['Para evitar parásitos conviene...', 'Lavarse las manos y lavar las verduras', ['Comer sin lavar', 'Tomar agua de río', 'Caminar descalzo en la basura'], 'La higiene es la mejor prevención.'],
                ['Una forma de contagiarse de parásitos es...', 'Comer o beber algo contaminado', ['Dormir mucho', 'Hacer deporte', 'Mirar televisión'], 'Agua y alimentos sin lavar o sin hervir transmiten parásitos.'],
                ['El agua que se toma de un grifo dudoso conviene...', 'Hervirla o purificarla', ['Tomarla fría', 'Mezclarla con refresco', 'Tomarla de golpe'], 'Hervirla mata los parásitos y microbios.'],
                ['Los niños se desparasitan...', 'Cada cierto tiempo, según el personal de salud', ['Nunca', 'Todos los días', 'Solo en Navidad'], 'En los controles de salud se indica cuándo hacerlo.'],
            ],
            'general' => [
                ['Los medicamentos deben guardarse en un lugar...', 'Fresco, seco y lejos de los niños', ['Al sol, en la ventana', 'En el baño, junto a la ducha', 'Al alcance de los niños'], 'El calor y la humedad estropean los medicamentos.'],
                ['Antes de tomar un medicamento conviene revisar...', 'La fecha de vencimiento', ['El color del empaque', 'El precio', 'El nombre de la farmacia'], 'Un medicamento vencido ya no es seguro ni efectivo.'],
                ['Si dudas cómo tomar un medicamento, preguntas a...', 'Un farmacéutico o médico', ['Un amigo', 'Una red social', 'El primer desconocido'], 'Ellos te dan indicaciones seguras.'],
                ['La automedicación es...', 'Tomar medicamentos sin que un profesional lo indique', ['Ir al médico cada mes', 'Leer el empaque', 'Comprar con receta'], 'Puede ser peligrosa aunque el medicamento parezca simple.'],
                ['Un medicamento vencido se debe...', 'Desechar y no usar', ['Tomar el doble', 'Regalar', 'Guardar para después'], 'Ya no garantiza sus efectos.'],
            ],
        ];
    }
}
